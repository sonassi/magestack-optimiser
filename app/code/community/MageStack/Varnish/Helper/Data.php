<?php

class MageStack_Varnish_Helper_Data extends Mage_Core_Helper_Abstract
{

    /**
     * Check if Varnish Cache is turned on
     *
     * @return boolean
     */
    public function useVarnishCache()
    {
        return Mage::app()->useCache('varnish');
    }

    /**
     * Return varnish servers from configuration
     *
     * @return array
     */
    public function getVarnishServers()
    {
        $serverConfig = Mage::getStoreConfig('varnish/options/servers');
        $varnishServers = array();

        foreach (explode(',', $serverConfig) as $value ) {
            $varnishServers[] = trim($value);
        }

        return $varnishServers;
    }

    /**
     * Create a queue of urls on all varnish servers.
     *
     * @param array $urls
     * @return array with errors
     */
    public function purge(array $urls)
    {
        file_put_contents($this->_getPurgeListPath(), "\n".implode("\n", $urls), FILE_APPEND);
        return array();
    }

    /**
     * Send a purge to all caches on all Varnish servers.
     *
     * @return array errors if any
     */
    public function purgeEverything()
    {
        return $this->purge(array('/.*'));
    }

    /**
     * Purge an array of urls on all varnish servers.
     *
     * @param array $urls
     * @return array with all errors
     */
    public function purgeProcess()
    {
        $urls = array_filter(array_unique(explode("\n", file_get_contents($this->_getPurgeListPath()))));

        $varnishServers = $this->getVarnishServers();
        $errors = array();

        // Init curl handler
        $curlHandlers = array(); // keep references for clean up
        $mh = curl_multi_init();

        foreach ((array)$varnishServers as $varnishServer) {
            foreach (array('http', 'https') as $scheme) {
                foreach ($urls as $url) {
                    $urlParts = parse_url($varnishServer);
                    $varnishUrl = $scheme . "://" . $urlParts['host'] . $url;

                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $varnishUrl);
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PURGE');
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                    //curl_setopt($ch, CURLOPT_HTTPHEADER, array('Host: ' . $varnishServer));

                    curl_multi_add_handle($mh, $ch);
                    $curlHandlers[] = $ch;
                }
            }
        }

        do {
            $n = curl_multi_exec($mh, $active);
        } while ($active);

        // Error handling and clean up
        foreach ($curlHandlers as $ch) {
            $info = curl_getinfo($ch);

            if (curl_errno($ch)) {
                $errors[] = "Cannot purge url {$info['url']} due to error" . curl_error($ch);
            } else if ($info['http_code'] != 200 && $info['http_code'] != 404) {
                $errors[] = "Cannot purge url {$info['url']}, http code: {$info['http_code']}";
            }

            curl_multi_remove_handle($mh, $ch);
            curl_close($ch);
        }
        curl_multi_close($mh);

        if (!count($errors))
            file_put_contents($this->_getPurgeListPath(), '');

        return $errors;
    }

    /**
     * Return path to file containing relative URIs to purge.
     *
     * @return file path
     */
    private function _getPurgeListPath()
    {
        return Mage::getBaseDir('log').DS.'varnish_purge.lst';
    }
}
