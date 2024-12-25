<nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

    <!-- Sidebar Toggle (Topbar) -->
    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
        <i class="fa fa-bars"></i>
    </button>
    <?php $env = strtolower(getenv('APPLICATION_ENV')); if($env &&  $env === 'development'):?>
        <p style="font-weight:bold;background:red;color:white;padding:1rem;text-align:center;margin:0 auto; width:300px;">TEST</p>
    <?php endif;?>

    <!-- Topbar Navbar -->
    <ul class="navbar-nav ml-auto">

        <?php if ($loggedIn): ?>
        <!-- Nav Item - User Information -->
        <li class="nav-item dropdown no-arrow">
            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?=$loggedInEmail?></span>
                <img class="img-profile rounded-circle" src="https://source.unsplash.com/QAB-WJcbgJk/60x60">
            </a>
            <!-- Dropdown - User Information -->
            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                <a class="dropdown-item" href="/user/form/<?=$loggedIn?>/">
                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                    <?=$this->t('Profile')?>
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="/login/logout/">
                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                    <?=$this->t('Logout')?>
                </a>
            </div>
        </li>
        <?php endif; ?>
    </ul>
</nav>