        <?php

/**
 * Define routes here.
 *
 * Routes follow this format:
 *
 * [METHOD, ROUTE, CALLABLE] or
 * [METHOD, ROUTE, [Class => method]]
 *
 * When controller is used without method (as string), it needs to have a magic __invoke method defined.
 *
 * Routes can use optional segments and regular expressions. See nikic/fastroute
 */
//@TODO find a proper way to use adminPath
/**
 * @var $adminPath string secret path to admin
 */
return [

    // backend
    [['GET'], '/', \EcomHelper\Backend\Action\Index::class],
    [['GET'], '/index/{action}/', \EcomHelper\Backend\Controller\IndexController::class],
    [['GET', 'POST'], '/post/{action}/[{id}/]', \EcomHelper\Backend\Controller\PostController::class],

    [['GET', 'POST'], '/login/{action}/', \Skeletor\Login\Controller\LoginController::class],
    [['GET', 'POST'], '/user/{action}/[{id}/]', \Skeletor\User\Controller\UserController::class],
    [['GET', 'POST'], '/image/{action}/[{id}/]', \Skeletor\Image\Controller\ImageController::class],
    [['GET', 'POST'], '/tag/{action}/[{id}/]', \Skeletor\Tag\Controller\TagController::class],
    [['GET', 'POST'], '/tenant/{action}/[{id}/]', \EcomHelper\Backend\Controller\TenantController::class],
    [['GET', 'POST'], '/supplier/{action}/[{id}/]', \EcomHelper\Backend\Controller\SupplierController::class],
    [['GET', 'POST'], '/source-product/{action}/[{id}/]', \EcomHelper\Backend\Controller\SourceProductController::class],
    [['GET', 'POST'], '/parsed-product/{action}/[{id}/]', \EcomHelper\Backend\Controller\ParsedProductController::class],
    // ex internal mapping
    [['GET', 'POST'], '/supplier-field-mapping/{action}/[{id}/]', \EcomHelper\Backend\Controller\SupplierFieldsMappingController::class],


    [['GET', 'POST'], '/activity/{action}/[{id}/]', \EcomHelper\Backend\Controller\ActivityController::class],

    [['GET', 'POST'], '/product/{action}/[{id}/]', \EcomHelper\Backend\Controller\ProductController::class],
    [['GET', 'POST'], '/productImport/{action}/[{id}/]', \EcomHelper\Backend\Controller\ProductImportController::class],


    [['GET', 'POST'], '/category/{action}/[{id}/]', \EcomHelper\Backend\Controller\CategoryController::class],
    [['GET', 'POST'], '/category-tenant-settings/{action}/[{id}/]', \EcomHelper\Backend\Controller\CategoryTenantSettingsController::class],
    [['GET', 'POST'], '/category-mapper/{action}/[{id}/[{mappingId}/]]', \EcomHelper\Backend\Controller\CategoryMapperController::class],
    [['GET', 'POST'], '/attribute-group/{action}/[{id}/]', \EcomHelper\Backend\Controller\AttributeGroupController::class],
    [['GET', 'POST'], '/attribute/{action}/[{id}/]', \EcomHelper\Backend\Controller\AttributeController::class],
    [['GET', 'POST'], '/attribute-values/{action}/[{id}/]', \EcomHelper\Backend\Controller\AttributeValues::class],

    [['GET', 'POST'], '/margin-groups/{action}/[{id}/]', \EcomHelper\Backend\Controller\MarginGroups::class],

    [['GET', 'POST'], '/category-parsing-rules/{action}/[{id}/]', \EcomHelper\Backend\Controller\CategoryParsingRuleController::class],
    [['GET', 'POST'], '/attribute-mapping/{action}/[{id}/]', \EcomHelper\Backend\Controller\SupplierAttributeMapping::class],
    [['GET'], '/scraper/{action}/', \EcomHelper\Backend\Controller\AttributeScraper::class],

    [['GET'], '/feeder/parse/{supplier}/', \EcomHelper\Backend\Action\Feeder\Parse::class],
    [['GET'], '/feeder/create/{supplier}/', \EcomHelper\Backend\Action\Feeder\Create::class],
    [['GET'], '/feeder/update/{supplier}/', \EcomHelper\Backend\Action\Feeder\Update::class],
    [['GET'], '/items/by-categories/{categoryIds}/', \EcomHelper\Backend\Action\Items::class],
    [['POST'], '/items/by-id/{tenantId}/', \EcomHelper\Backend\Action\Item::class],
    [['GET'], '/categories/[{tenantId}/]', \EcomHelper\Backend\Action\Categories::class],
    [['GET', 'POST'], '/fixImages/', \EcomHelper\Backend\Action\FixProductImages::class],
    [['GET'], '/feedStart/{supplier}/', \EcomHelper\Backend\Action\Feeder\FeedStart::class],

];
