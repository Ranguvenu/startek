<?php return array(
    'root' => array(
        'name' => 'moodle/moodle',
        'pretty_version' => '1.0.0+no-version-set',
        'version' => '1.0.0.0',
        'reference' => null,
        'type' => 'project',
        'install_path' => __DIR__ . '/../../',
        'aliases' => array(),
        'dev' => true,
    ),
    'versions' => array(
        'behat/behat' => array(
            'pretty_version' => 'v3.13.0',
            'version' => '3.13.0.0',
            'reference' => '9dd7cdb309e464ddeab095cd1a5151c2dccba4ab',
            'type' => 'library',
            'install_path' => __DIR__ . '/../behat/behat',
            'aliases' => array(),
            'dev_requirement' => true,
        ),
        'behat/gherkin' => array(
            'pretty_version' => 'v4.9.0',
            'version' => '4.9.0.0',
            'reference' => '0bc8d1e30e96183e4f36db9dc79caead300beff4',
            'type' => 'library',
            'install_path' => __DIR__ . '/../behat/gherkin',
            'aliases' => array(),
            'dev_requirement' => true,
        ),
        'behat/mink' => array(
            'pretty_version' => 'v1.10.0',
            'version' => '1.10.0.0',
            'reference' => '19e58905632e7cfdc5b2bafb9b950a3521af32c5',
            'type' => 'library',
            'install_path' => __DIR__ . '/../behat/mink',
            'aliases' => array(),
            'dev_requirement' => true,
        ),
        'behat/mink-browserkit-driver' => array(
            'pretty_version' => 'v2.1.0',
            'version' => '2.1.0.0',
            'reference' => 'd2768e6c17b293d86d8fcff54cbb9e6ad938fee1',
            'type' => 'mink-driver',
            'install_path' => __DIR__ . '/../behat/mink-browserkit-driver',
            'aliases' => array(),
            'dev_requirement' => true,
        ),
        'behat/mink-extension' => array(
            'dev_requirement' => true,
            'replaced' => array(
                0 => 'v2.7.2',
            ),
        ),
        'behat/transliterator' => array(
            'pretty_version' => 'v1.5.0',
            'version' => '1.5.0.0',
            'reference' => 'baac5873bac3749887d28ab68e2f74db3a4408af',
            'type' => 'library',
            'install_path' => __DIR__ . '/../behat/transliterator',
            'aliases' => array(),
            'dev_requirement' => true,
        ),
        'doctrine/instantiator' => array(
            'pretty_version' => '1.5.0',
            'version' => '1.5.0.0',
            'reference' => '0a0fa9780f5d4e507415a065172d26a98d02047b',
            'type' => 'library',
            'install_path' => __DIR__ . '/../doctrine/instantiator',
            'aliases' => array(),
            'dev_requirement' => true,
        ),
        'facebook/webdriver' => array(
            'dev_requirement' => true,
            'replaced' => array(
                0 => '*',
            ),
        ),
        'friends-of-behat/mink-extension' => array(
            'pretty_version' => 'v2.7.2',
            'version' => '2.7.2.0',
            'reference' => 'ffc5ee88aa8e5b430f0c417adb3f0c943ffeafed',
            'type' => 'behat-extension',
            'install_path' => __DIR__ . '/../friends-of-behat/mink-extension',
            'aliases' => array(),
            'dev_requirement' => true,
        ),
        'mikey179/vfsstream' => array(
            'pretty_version' => 'v1.6.11',
            'version' => '1.6.11.0',
            'reference' => '17d16a85e6c26ce1f3e2fa9ceeacdc2855db1e9f',
            'type' => 'library',
            'install_path' => __DIR__ . '/../mikey179/vfsstream',
            'aliases' => array(),
            'dev_requirement' => true,
        ),
        'moodle/moodle' => array(
            'pretty_version' => '1.0.0+no-version-set',
            'version' => '1.0.0.0',
            'reference' => null,
            'type' => 'project',
            'install_path' => __DIR__ . '/../../',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'myclabs/deep-copy' => array(
            'pretty_version' => '1.11.1',
            'version' => '1.11.1.0',
            'reference' => '7284c22080590fb39f2ffa3e9057f10a4ddd0e0c',
            'type' => 'library',
            'install_path' => __DIR__ . '/../myclabs/deep-copy',
            'aliases' => array(),
            'dev_requirement' => true,
        ),
        'nikic/php-parser' => array(
            'pretty_version' => 'v4.16.0',
            'version' => '4.16.0.0',
            'reference' => '19526a33fb561ef417e822e85f08a00db4059c17',
            'type' => 'library',
            'install_path' => __DIR__ . '/../nikic/php-parser',
            'aliases' => array(),
            'dev_requirement' => true,
        ),
        'oleg-andreyev/mink-phpwebdriver' => array(
            'pretty_version' => 'v1.2.1',
            'version' => '1.2.1.0',
            'reference' => 'e265917faf79b649f4e5d4419325e02c096caec2',
            'type' => 'mink-driver',
            'install_path' => __DIR__ . '/../oleg-andreyev/mink-phpwebdriver',
            'aliases' => array(),
            'dev_requirement' => true,
        ),
        'phar-io/manifest' => array(
            'pretty_version' => '2.0.3',
            'version' => '2.0.3.0',
            'reference' => '97803eca37d319dfa7826cc2437fc020857acb53',
            'type' => 'library',
            'install_path' => __DIR__ . '/../phar-io/manifest',
            'aliases' => array(),
            'dev_requirement' => true,
        ),
        'phar-io/version' => array(
            'pretty_version' => '3.2.1',
            'version' => '3.2.1.0',
            'reference' => '4f7fd7836c6f332bb2933569e566a0d6c4cbed74',
            'type' => 'library',
            'install_path' => __DIR__ . '/../phar-io/version',
            'aliases' => array(),
            'dev_requirement' => true,
        ),
        'php-http/async-client-implementation' => array(
            'dev_requirement' => true,
            'provided' => array(
                0 => '*',
            ),
        ),
        'php-http/client-implementation' => array(
            'dev_requirement' => true,
            'provided' => array(
                0 => '*',
            ),
        ),
        'php-webdriver/webdriver' => array(
            'pretty_version' => '1.14.0',
            'version' => '1.14.0.0',
            'reference' => '3ea4f924afb43056bf9c630509e657d951608563',
            'type' => 'library',
            'install_path' => __DIR__ . '/../php-webdriver/webdriver',
            'aliases' => array(),
            'dev_requirement' => true,
        ),
        'phpstan/phpstan' => array(
            'pretty_version' => '1.12.4',
            'version' => '1.12.4.0',
            'reference' => 'ffa517cb918591b93acc9b95c0bebdcd0e4538bd',
            'type' => 'library',
            'install_path' => __DIR__ . '/../phpstan/phpstan',
            'aliases' => array(),
            'dev_requirement' => true,
        ),
        'phpunit/php-code-coverage' => array(
            'pretty_version' => '9.2.27',
            'version' => '9.2.27.0',
            'reference' => 'b0a88255cb70d52653d80c890bd7f38740ea50d1',
            'type' => 'library',
            'install_path' => __DIR__ . '/../phpunit/php-code-coverage',
            'aliases' => array(),
            'dev_requirement' => true,
        ),
        'phpunit/php-file-iterator' => array(
            'pretty_version' => '3.0.6',
            'version' => '3.0.6.0',
            'reference' => 'cf1c2e7c203ac650e352f4cc675a7021e7d1b3cf',
            'type' => 'library',
            'install_path' => __DIR__ . '/../phpunit/php-file-iterator',
            'aliases' => array(),
            'dev_requirement' => true,
        ),
        'phpunit/php-invoker' => array(
            'pretty_version' => '3.1.1',
            'version' => '3.1.1.0',
            'reference' => '5a10147d0aaf65b58940a0b72f71c9ac0423cc67',
            'type' => 'library',
            'install_path' => __DIR__ . '/../phpunit/php-invoker',
            'aliases' => array(),
            'dev_requirement' => true,
        ),
        'phpunit/php-text-template' => array(
            'pretty_version' => '2.0.4',
            'version' => '2.0.4.0',
            'reference' => '5da5f67fc95621df9ff4c4e5a84d6a8a2acf7c28',
            'type' => 'library',
            'install_path' => __DIR__ . '/../phpunit/php-text-template',
            'aliases' => array(),
            'dev_requirement' => true,
        ),
        'phpunit/php-timer' => array(
            'pretty_version' => '5.0.3',
            'version' => '5.0.3.0',
            'reference' => '5a63ce20ed1b5bf577850e2c4e87f4aa902afbd2',
            'type' => 'library',
            'install_path' => __DIR__ . '/../phpunit/php-timer',
            'aliases' => array(),
            'dev_requirement' => true,
        ),
        'phpunit/phpunit' => array(
            'pretty_version' => '9.5.28',
            'version' => '9.5.28.0',
            'reference' => '954ca3113a03bf780d22f07bf055d883ee04b65e',
            'type' => 'library',
            'install_path' => __DIR__ . '/../phpunit/phpunit',
            'aliases' => array(),
            'dev_requirement' => true,
        ),
        'psr/container' => array(
            'pretty_version' => '2.0.2',
            'version' => '2.0.2.0',
            'reference' => 'c71ecc56dfe541dbd90c5360474fbc405f8d5963',
            'type' => 'library',
            'install_path' => __DIR__ . '/../psr/container',
            'aliases' => array(),
            'dev_requirement' => true,
        ),
        'psr/container-implementation' => array(
            'dev_requirement' => true,
            'provided' => array(
                0 => '1.1|2.0',
            ),
        ),
        'psr/event-dispatcher' => array(
            'pretty_version' => '1.0.0',
            'version' => '1.0.0.0',
            'reference' => 'dbefd12671e8a14ec7f180cab83036ed26714bb0',
            'type' => 'library',
            'install_path' => __DIR__ . '/../psr/event-dispatcher',
            'aliases' => array(),
            'dev_requirement' => true,
        ),
        'psr/event-dispatcher-implementation' => array(
            'dev_requirement' => true,
            'provided' => array(
                0 => '1.0',
            ),
        ),
        'psr/http-client-implementation' => array(
            'dev_requirement' => true,
            'provided' => array(
                0 => '1.0',
            ),
        ),
        'psr/log' => array(
            'pretty_version' => '3.0.0',
            'version' => '3.0.0.0',
            'reference' => 'fe5ea303b0887d5caefd3d431c3e61ad47037001',
            'type' => 'library',
            'install_path' => __DIR__ . '/../psr/log',
            'aliases' => array(),
            'dev_requirement' => true,
        ),
        'psr/log-implementation' => array(
            'dev_requirement' => true,
            'provided' => array(
                0 => '1.0|2.0|3.0',
            ),
        ),
        'sebastian/cli-parser' => array(
            'pretty_version' => '1.0.1',
            'version' => '1.0.1.0',
            'reference' => '442e7c7e687e42adc03470c7b668bc4b2402c0b2',
            'type' => 'library',
            'install_path' => __DIR__ . '/../sebastian/cli-parser',
            'aliases' => array(),
            'dev_requirement' => true,
        ),
        'sebastian/code-unit' => array(
            'pretty_version' => '1.0.8',
            'version' => '1.0.8.0',
            'reference' => '1fc9f64c0927627ef78ba436c9b17d967e68e120',
            'type' => 'library',
            'install_path' => __DIR__ . '/../sebastian/code-unit',
            'aliases' => array(),
            'dev_requirement' => true,
        ),
        'sebastian/code-unit-reverse-lookup' => array(
            'pretty_version' => '2.0.3',
            'version' => '2.0.3.0',
            'reference' => 'ac91f01ccec49fb77bdc6fd1e548bc70f7faa3e5',
            'type' => 'library',
            'install_path' => __DIR__ . '/../sebastian/code-unit-reverse-lookup',
            'aliases' => array(),
            'dev_requirement' => true,
        ),
        'sebastian/comparator' => array(
            'pretty_version' => '4.0.8',
            'version' => '4.0.8.0',
            'reference' => 'fa0f136dd2334583309d32b62544682ee972b51a',
            'type' => 'library',
            'install_path' => __DIR__ . '/../sebastian/comparator',
            'aliases' => array(),
            'dev_requirement' => true,
        ),
        'sebastian/complexity' => array(
            'pretty_version' => '2.0.2',
            'version' => '2.0.2.0',
            'reference' => '739b35e53379900cc9ac327b2147867b8b6efd88',
            'type' => 'library',
            'install_path' => __DIR__ . '/../sebastian/complexity',
            'aliases' => array(),
            'dev_requirement' => true,
        ),
        'sebastian/diff' => array(
            'pretty_version' => '4.0.5',
            'version' => '4.0.5.0',
            'reference' => '74be17022044ebaaecfdf0c5cd504fc9cd5a7131',
            'type' => 'library',
            'install_path' => __DIR__ . '/../sebastian/diff',
            'aliases' => array(),
            'dev_requirement' => true,
        ),
        'sebastian/environment' => array(
            'pretty_version' => '5.1.5',
            'version' => '5.1.5.0',
            'reference' => '830c43a844f1f8d5b7a1f6d6076b784454d8b7ed',
            'type' => 'library',
            'install_path' => __DIR__ . '/../sebastian/environment',
            'aliases' => array(),
            'dev_requirement' => true,
        ),
        'sebastian/exporter' => array(
            'pretty_version' => '4.0.5',
            'version' => '4.0.5.0',
            'reference' => 'ac230ed27f0f98f597c8a2b6eb7ac563af5e5b9d',
            'type' => 'library',
            'install_path' => __DIR__ . '/../sebastian/exporter',
            'aliases' => array(),
            'dev_requirement' => true,
        ),
        'sebastian/global-state' => array(
            'pretty_version' => '5.0.6',
            'version' => '5.0.6.0',
            'reference' => 'bde739e7565280bda77be70044ac1047bc007e34',
            'type' => 'library',
            'install_path' => __DIR__ . '/../sebastian/global-state',
            'aliases' => array(),
            'dev_requirement' => true,
        ),
        'sebastian/lines-of-code' => array(
            'pretty_version' => '1.0.3',
            'version' => '1.0.3.0',
            'reference' => 'c1c2e997aa3146983ed888ad08b15470a2e22ecc',
            'type' => 'library',
            'install_path' => __DIR__ . '/../sebastian/lines-of-code',
            'aliases' => array(),
            'dev_requirement' => true,
        ),
        'sebastian/object-enumerator' => array(
            'pretty_version' => '4.0.4',
            'version' => '4.0.4.0',
            'reference' => '5c9eeac41b290a3712d88851518825ad78f45c71',
            'type' => 'library',
            'install_path' => __DIR__ . '/../sebastian/object-enumerator',
            'aliases' => array(),
            'dev_requirement' => true,
        ),
        'sebastian/object-reflector' => array(
            'pretty_version' => '2.0.4',
            'version' => '2.0.4.0',
            'reference' => 'b4f479ebdbf63ac605d183ece17d8d7fe49c15c7',
            'type' => 'library',
            'install_path' => __DIR__ . '/../sebastian/object-reflector',
            'aliases' => array(),
            'dev_requirement' => true,
        ),
        'sebastian/recursion-context' => array(
            'pretty_version' => '4.0.5',
            'version' => '4.0.5.0',
            'reference' => 'e75bd0f07204fec2a0af9b0f3cfe97d05f92efc1',
            'type' => 'library',
            'install_path' => __DIR__ . '/../sebastian/recursion-context',
            'aliases' => array(),
            'dev_requirement' => true,
        ),
        'sebastian/resource-operations' => array(
            'pretty_version' => '3.0.3',
            'version' => '3.0.3.0',
            'reference' => '0f4443cb3a1d92ce809899753bc0d5d5a8dd19a8',
            'type' => 'library',
            'install_path' => __DIR__ . '/../sebastian/resource-operations',
            'aliases' => array(),
            'dev_requirement' => true,
        ),
        'sebastian/type' => array(
            'pretty_version' => '3.2.1',
            'version' => '3.2.1.0',
            'reference' => '75e2c2a32f5e0b3aef905b9ed0b179b953b3d7c7',
            'type' => 'library',
            'install_path' => __DIR__ . '/../sebastian/type',
            'aliases' => array(),
            'dev_requirement' => true,
        ),
        'sebastian/version' => array(
            'pretty_version' => '3.0.2',
            'version' => '3.0.2.0',
            'reference' => 'c6c1022351a901512170118436c764e473f6de8c',
            'type' => 'library',
            'install_path' => __DIR__ . '/../sebastian/version',
            'aliases' => array(),
            'dev_requirement' => true,
        ),
        'symfony/browser-kit' => array(
            'pretty_version' => 'v6.0.19',
            'version' => '6.0.19.0',
            'reference' => '4d1bf7886e2af0a194332486273debcd6662cfc9',
            'type' => 'library',
            'install_path' => __DIR__ . '/../symfony/browser-kit',
            'aliases' => array(),
            'dev_requirement' => true,
        ),
        'symfony/config' => array(
            'pretty_version' => 'v6.0.19',
            'version' => '6.0.19.0',
            'reference' => 'db4fc45c24e0c3e2198e68ada9d7f90daa1f97e3',
            'type' => 'library',
            'install_path' => __DIR__ . '/../symfony/config',
            'aliases' => array(),
            'dev_requirement' => true,
        ),
        'symfony/console' => array(
            'pretty_version' => 'v6.0.19',
            'version' => '6.0.19.0',
            'reference' => 'c3ebc83d031b71c39da318ca8b7a07ecc67507ed',
            'type' => 'library',
            'install_path' => __DIR__ . '/../symfony/console',
            'aliases' => array(),
            'dev_requirement' => true,
        ),
        'symfony/css-selector' => array(
            'pretty_version' => 'v6.0.19',
            'version' => '6.0.19.0',
            'reference' => 'f1d00bddb83a4cb2138564b2150001cb6ce272b1',
            'type' => 'library',
            'install_path' => __DIR__ . '/../symfony/css-selector',
            'aliases' => array(),
            'dev_requirement' => true,
        ),
        'symfony/dependency-injection' => array(
            'pretty_version' => 'v6.0.20',
            'version' => '6.0.20.0',
            'reference' => '359806e1adebd1c43e18e5ea22acd14bef7fcf8c',
            'type' => 'library',
            'install_path' => __DIR__ . '/../symfony/dependency-injection',
            'aliases' => array(),
            'dev_requirement' => true,
        ),
        'symfony/deprecation-contracts' => array(
            'pretty_version' => 'v3.0.2',
            'version' => '3.0.2.0',
            'reference' => '26954b3d62a6c5fd0ea8a2a00c0353a14978d05c',
            'type' => 'library',
            'install_path' => __DIR__ . '/../symfony/deprecation-contracts',
            'aliases' => array(),
            'dev_requirement' => true,
        ),
        'symfony/dom-crawler' => array(
            'pretty_version' => 'v6.0.19',
            'version' => '6.0.19.0',
            'reference' => '622578ff158318b1b49d95068bd6b66c713601e9',
            'type' => 'library',
            'install_path' => __DIR__ . '/../symfony/dom-crawler',
            'aliases' => array(),
            'dev_requirement' => true,
        ),
        'symfony/event-dispatcher' => array(
            'pretty_version' => 'v6.0.19',
            'version' => '6.0.19.0',
            'reference' => '2eaf8e63bc5b8cefabd4a800157f0d0c094f677a',
            'type' => 'library',
            'install_path' => __DIR__ . '/../symfony/event-dispatcher',
            'aliases' => array(),
            'dev_requirement' => true,
        ),
        'symfony/event-dispatcher-contracts' => array(
            'pretty_version' => 'v3.0.2',
            'version' => '3.0.2.0',
            'reference' => '7bc61cc2db649b4637d331240c5346dcc7708051',
            'type' => 'library',
            'install_path' => __DIR__ . '/../symfony/event-dispatcher-contracts',
            'aliases' => array(),
            'dev_requirement' => true,
        ),
        'symfony/event-dispatcher-implementation' => array(
            'dev_requirement' => true,
            'provided' => array(
                0 => '2.0|3.0',
            ),
        ),
        'symfony/filesystem' => array(
            'pretty_version' => 'v6.0.19',
            'version' => '6.0.19.0',
            'reference' => '3d49eec03fda1f0fc19b7349fbbe55ebc1004214',
            'type' => 'library',
            'install_path' => __DIR__ . '/../symfony/filesystem',
            'aliases' => array(),
            'dev_requirement' => true,
        ),
        'symfony/http-client' => array(
            'pretty_version' => 'v6.0.20',
            'version' => '6.0.20.0',
            'reference' => '541c04560da1875f62c963c3aab6ea12a7314e11',
            'type' => 'library',
            'install_path' => __DIR__ . '/../symfony/http-client',
            'aliases' => array(),
            'dev_requirement' => true,
        ),
        'symfony/http-client-contracts' => array(
            'pretty_version' => 'v3.0.2',
            'version' => '3.0.2.0',
            'reference' => '4184b9b63af1edaf35b6a7974c6f1f9f33294129',
            'type' => 'library',
            'install_path' => __DIR__ . '/../symfony/http-client-contracts',
            'aliases' => array(),
            'dev_requirement' => true,
        ),
        'symfony/http-client-implementation' => array(
            'dev_requirement' => true,
            'provided' => array(
                0 => '3.0',
            ),
        ),
        'symfony/mime' => array(
            'pretty_version' => 'v6.0.19',
            'version' => '6.0.19.0',
            'reference' => 'd7052547a0070cbeadd474e172b527a00d657301',
            'type' => 'library',
            'install_path' => __DIR__ . '/../symfony/mime',
            'aliases' => array(),
            'dev_requirement' => true,
        ),
        'symfony/polyfill-ctype' => array(
            'pretty_version' => 'v1.27.0',
            'version' => '1.27.0.0',
            'reference' => '5bbc823adecdae860bb64756d639ecfec17b050a',
            'type' => 'library',
            'install_path' => __DIR__ . '/../symfony/polyfill-ctype',
            'aliases' => array(),
            'dev_requirement' => true,
        ),
        'symfony/polyfill-intl-grapheme' => array(
            'pretty_version' => 'v1.27.0',
            'version' => '1.27.0.0',
            'reference' => '511a08c03c1960e08a883f4cffcacd219b758354',
            'type' => 'library',
            'install_path' => __DIR__ . '/../symfony/polyfill-intl-grapheme',
            'aliases' => array(),
            'dev_requirement' => true,
        ),
        'symfony/polyfill-intl-idn' => array(
            'pretty_version' => 'v1.27.0',
            'version' => '1.27.0.0',
            'reference' => '639084e360537a19f9ee352433b84ce831f3d2da',
            'type' => 'library',
            'install_path' => __DIR__ . '/../symfony/polyfill-intl-idn',
            'aliases' => array(),
            'dev_requirement' => true,
        ),
        'symfony/polyfill-intl-normalizer' => array(
            'pretty_version' => 'v1.27.0',
            'version' => '1.27.0.0',
            'reference' => '19bd1e4fcd5b91116f14d8533c57831ed00571b6',
            'type' => 'library',
            'install_path' => __DIR__ . '/../symfony/polyfill-intl-normalizer',
            'aliases' => array(),
            'dev_requirement' => true,
        ),
        'symfony/polyfill-mbstring' => array(
            'pretty_version' => 'v1.27.0',
            'version' => '1.27.0.0',
            'reference' => '8ad114f6b39e2c98a8b0e3bd907732c207c2b534',
            'type' => 'library',
            'install_path' => __DIR__ . '/../symfony/polyfill-mbstring',
            'aliases' => array(),
            'dev_requirement' => true,
        ),
        'symfony/polyfill-php72' => array(
            'pretty_version' => 'v1.27.0',
            'version' => '1.27.0.0',
            'reference' => '869329b1e9894268a8a61dabb69153029b7a8c97',
            'type' => 'library',
            'install_path' => __DIR__ . '/../symfony/polyfill-php72',
            'aliases' => array(),
            'dev_requirement' => true,
        ),
        'symfony/polyfill-php81' => array(
            'pretty_version' => 'v1.27.0',
            'version' => '1.27.0.0',
            'reference' => '707403074c8ea6e2edaf8794b0157a0bfa52157a',
            'type' => 'library',
            'install_path' => __DIR__ . '/../symfony/polyfill-php81',
            'aliases' => array(),
            'dev_requirement' => true,
        ),
        'symfony/process' => array(
            'pretty_version' => 'v6.0.19',
            'version' => '6.0.19.0',
            'reference' => '2114fd60f26a296cc403a7939ab91478475a33d4',
            'type' => 'library',
            'install_path' => __DIR__ . '/../symfony/process',
            'aliases' => array(),
            'dev_requirement' => true,
        ),
        'symfony/service-contracts' => array(
            'pretty_version' => 'v3.0.2',
            'version' => '3.0.2.0',
            'reference' => 'd78d39c1599bd1188b8e26bb341da52c3c6d8a66',
            'type' => 'library',
            'install_path' => __DIR__ . '/../symfony/service-contracts',
            'aliases' => array(),
            'dev_requirement' => true,
        ),
        'symfony/service-implementation' => array(
            'dev_requirement' => true,
            'provided' => array(
                0 => '1.1|2.0|3.0',
            ),
        ),
        'symfony/string' => array(
            'pretty_version' => 'v6.0.19',
            'version' => '6.0.19.0',
            'reference' => 'd9e72497367c23e08bf94176d2be45b00a9d232a',
            'type' => 'library',
            'install_path' => __DIR__ . '/../symfony/string',
            'aliases' => array(),
            'dev_requirement' => true,
        ),
        'symfony/translation' => array(
            'pretty_version' => 'v6.0.19',
            'version' => '6.0.19.0',
            'reference' => '9c24b3fdbbe9fb2ef3a6afd8bbaadfd72dad681f',
            'type' => 'library',
            'install_path' => __DIR__ . '/../symfony/translation',
            'aliases' => array(),
            'dev_requirement' => true,
        ),
        'symfony/translation-contracts' => array(
            'pretty_version' => 'v3.0.2',
            'version' => '3.0.2.0',
            'reference' => 'acbfbb274e730e5a0236f619b6168d9dedb3e282',
            'type' => 'library',
            'install_path' => __DIR__ . '/../symfony/translation-contracts',
            'aliases' => array(),
            'dev_requirement' => true,
        ),
        'symfony/translation-implementation' => array(
            'dev_requirement' => true,
            'provided' => array(
                0 => '2.3|3.0',
            ),
        ),
        'symfony/yaml' => array(
            'pretty_version' => 'v6.0.19',
            'version' => '6.0.19.0',
            'reference' => 'deec3a812a0305a50db8ae689b183f43d915c884',
            'type' => 'library',
            'install_path' => __DIR__ . '/../symfony/yaml',
            'aliases' => array(),
            'dev_requirement' => true,
        ),
        'theseer/tokenizer' => array(
            'pretty_version' => '1.2.1',
            'version' => '1.2.1.0',
            'reference' => '34a41e998c2183e22995f158c581e7b5e755ab9e',
            'type' => 'library',
            'install_path' => __DIR__ . '/../theseer/tokenizer',
            'aliases' => array(),
            'dev_requirement' => true,
        ),
    ),
);
