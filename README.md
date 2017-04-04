# MageStack Optimiser

This module aims to assist Magento 1.x store owners with fast/easy store improvements to address common store misconfiguration.

## Installation

 1. Change directory to your Magento document root

 1. Using `git`, clone the repository,

         git clone https://github.com/sonassi/magestack-optimiser

 1. Copy the respective files into your Magento installation

         rsync -vPa magestack-optimiser/app/ app/
         rsync -vPa magestack-optimiser/skin/ skin/

 1. Refresh your Magento configuration, layout and block HTML cache

 1. From the Magento admin, visit `System > MageStack > Optimiser` and follow the on screen instructions
