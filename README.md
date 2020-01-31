#Ltree extension bundle

This bundle grants access to PostgreSQL ltree methods
Based on [slev/ltree-extension-bundle](https://github.com/semin-lev/ltree-extension-bundle)

##Installation

```shell script
composer require lastov-dmitrii/ltree-extension-bundle
```

If you are not using symfony/flex, you just need to add the bundle to your `config/bundles.php`

```php
return [
    // ...
    DDL\LtreeExtensionBundle\LtreeExtensionBundle::class => ['all' => true],
    // ...
];
```

##DQL functions

| DQL            	| SQL                    	| Usage                                                 	|
|----------------	|------------------------	|-------------------------------------------------------	|
| ltree_nlevel   	| nlevel                 	| ltree_nlevel(:ltree) === 5                            	|
| ltree_concat   	| ||                     	| ltree_concat(:a, :b)                                  	|
| ltree_subpath  	| subpath                	| ltree_subpath(:ltree, 1); ltree_subpath(:ltree, 1, 1) 	|
| ltree_operator 	| ltree {operator} ltree 	| ltree_operator(m.path, '@>', :ltree)=TRUE             	|

## Doctrine Type

Adds `ltree` doctrine type;

```php
class Entity {
    /**
     * @var string[]
     *
     * @ORM\Column(type="ltree")
     */
    private $ltreePath = [];
}
```