<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion toggled" id="accordionSidebar">
    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="/">
        <div class="sidebar-brand-text mx-3">Konovo</div>
    </a>

<?php if($loggedIn): ?>
    <!-- Heading -->
    <div class="sidebar-heading"><?=$this->t('Dashboard')?></div>

    <li class="nav-item">
        <a class="nav-link collapsed" href="/product/view/">
            <i class="fas fa-fw fa-cog"></i>
            <span><?=$this->t('Products')?></span>
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseOne"
           aria-expanded="true" aria-controls="collapseOne">
<!--        <a class="nav-link collapsed" href="/category/viewStructure/">-->
            <i class="fas fa-fw fa-cog"></i>
            <span><?=$this->t('Categories')?></span>
        </a>
        <div id="collapseOne" class="collapse" aria-labelledby="headingOne" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item" href="/category/view/"><?=$this->t('View')?></a>
                <a class="collapse-item" href="/category-mapper/internalMapping/2/"><?=$this->t('Internal mapping')?></a>
                <a class="collapse-item" href="/category-mapper/externalMapping/-1/"><?=$this->t('External mapping')?></a>
            </div>
        </div>
    </li>
    <li class="nav-item">
        <a class="nav-link collapsed" href="/margin-groups/view/">
            <i class="fas fa-fw fa-cog"></i>
            <span><?=$this->t('Margin Groups')?></span>
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseThree"
           aria-expanded="true" aria-controls="collapseThree">
            <i class="fas fa-fw fa-cog"></i>
            <span><?=$this->t('Suppliers')?></span>
        </a>
        <div id="collapseThree" class="collapse" aria-labelledby="headingOne" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item" href="/supplier/view/"><?=$this->t('View')?></a>
                <a class="collapse-item" href="/supplier-field-mapping/view/"><?=$this->t('Product field mapping')?></a>
                <a class="collapse-item" href="/source-product/view/"><?=$this->t('Source product')?></a>
                <a class="collapse-item" href="/parsed-product/view/"><?=$this->t('Parsed product')?></a>
            </div>
        </div>
    </li>

    <li class="nav-item">
        <a class="nav-link collapsed" href="/category-parsing-rules/view/">
            <i class="fas fa-fw fa-cog"></i>
            <span><?=$this->t('Parsing rules')?></span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link collapsed" href="/attribute-mapping/view/">
            <i class="fas fa-fw fa-cog"></i>
            <span><?=$this->t('Attribute mapping')?></span>
        </a>
    </li>

    <li class="nav-item">
        <div id="mediaLibraryNavigation" title="<?=$this->t('Media')?>" class="mediaLibraryInitiator"
             data-insertable="false" data-multiple="false">
            <svg style="pointer-events:none;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512">
                <path d="M256 0H576c35.3 0 64 28.7 64 64V288c0 35.3-28.7 64-64 64H256c-35.3 0-64-28.7-64-64V64c0-35.3 28.7-64 64-64zM476 106.7C471.5 100 464 96 456 96s-15.5 4-20 10.7l-56 84L362.7 169c-4.6-5.7-11.5-9-18.7-9s-14.2 3.3-18.7 9l-64 80c-5.8 7.2-6.9 17.1-2.9 25.4s12.4 13.6 21.6 13.6h80 48H552c8.9 0 17-4.9 21.2-12.7s3.7-17.3-1.2-24.6l-96-144zM336 96a32 32 0 1 0 -64 0 32 32 0 1 0 64 0zM64 128h96V384v32c0 17.7 14.3 32 32 32H320c17.7 0 32-14.3 32-32V384H512v64c0 35.3-28.7 64-64 64H64c-35.3 0-64-28.7-64-64V192c0-35.3 28.7-64 64-64zm8 64c-8.8 0-16 7.2-16 16v16c0 8.8 7.2 16 16 16H88c8.8 0 16-7.2 16-16V208c0-8.8-7.2-16-16-16H72zm0 104c-8.8 0-16 7.2-16 16v16c0 8.8 7.2 16 16 16H88c8.8 0 16-7.2 16-16V312c0-8.8-7.2-16-16-16H72zm0 104c-8.8 0-16 7.2-16 16v16c0 8.8 7.2 16 16 16H88c8.8 0 16-7.2 16-16V416c0-8.8-7.2-16-16-16H72zm336 16v16c0 8.8 7.2 16 16 16h16c8.8 0 16-7.2 16-16V416c0-8.8-7.2-16-16-16H424c-8.8 0-16 7.2-16 16z"/>
            </svg>
            <span><?=$this->t('Media')?></span>
        </div>
    </li>

    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseTwo"
           aria-expanded="true" aria-controls="collapseOne">
            <i class="fas fa-fw fa-cog"></i>
            <span><?=$this->t('Attributes')?></span>
        </a>
        <div id="collapseTwo" class="collapse" aria-labelledby="headingOne" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item" href="/attribute/view/"><?=$this->t('View')?></a>
                <a class="collapse-item" href="/attribute-group/view/"><?=$this->t('Attribute Groups')?></a>
                <a class="collapse-item" href="/attribute/secondDescriptionSettings/"><?=$this->t('Second Description Settings')?></a>
                <a class="collapse-item" href="/attribute-values/view/"><?=$this->t('Attribute value settings')?></a>
            </div>
        </div>
    </li>
    <li class="nav-item">
        <a class="nav-link collapsed" href="/tag/view/">
            <i class="fas fa-fw fa-cog"></i>
            <span><?=$this->t('Tags')?></span>
        </a>
    </li>

    <hr class="sidebar-divider">

    <!-- Nav Item - Pages Collapse Menu -->
    <?php if($loggedInRole === \Skeletor\User\Model\User::ROLE_ADMIN): ?>
    <li class="nav-item">
        <a class="nav-link collapsed" href="/tenant/view/">
            <i class="fas fa-fw fa-cog"></i>
            <span><?=$this->t('Tenants')?></span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link collapsed" href="/user/view/">
            <i class="fas fa-fw fa-cog"></i>
            <span><?=$this->t('Users')?></span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link collapsed" href="/activity/view/">
            <i class="fas fa-fw fa-cog"></i>
            <span><?=$this->t('Activities')?></span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link collapsed" href="/translator/view/">
            <i class="fas fa-fw fa-cog"></i>
            <span><?=$this->t('Translations')?></span>
        </a>
    </li>
    <!-- Divider -->
    <hr class="sidebar-divider">
    <?php endif; ?>

    <!-- Divider -->
    <hr class="sidebar-divider">
<?php endif; ?>

    <!-- Sidebar Toggler (Sidebar) -->
    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>
</ul>