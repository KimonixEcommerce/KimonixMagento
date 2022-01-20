# Magento 2 [Kimonix](https://www.kimonix.com/) Module

Magento 2 module for integration with Kimonix.

---

## ✓ Install via composer (recommended)
Run the following command under your Magento 2 root dir:

```
composer require kimonix/magento2-module-kimonix
php bin/magento maintenance:enable
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy
php bin/magento maintenance:disable
php bin/magento cache:flush
```

## Install manually under app/code
Download & place the contents of this repository under {YOUR-MAGENTO2-ROOT-DIR}/app/code/Kimonix/Kimonix  
Then, run the following commands under your Magento 2 root dir:
```
php bin/magento maintenance:enable
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy
php bin/magento maintenance:disable
php bin/magento cache:flush
```

---

https://www.kimonix.com/

Copyright © 2022 Kimonix.

![Kimonix Logo](https://uploads-ssl.webflow.com/604359c9f6f92101d4ff93ef/612a5226d07911764721b1a5_kimonix%20logo.svg)
