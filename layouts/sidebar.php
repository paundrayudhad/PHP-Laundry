<!-- Sidebar -->
<div class="sidebar sidebar-style-2" >
        <div class="sidebar-logo">
          <!-- Logo Header -->
          <div class="logo-header" data-background-color="blue">
            <a href="" class="logo">
              <img
                src="<?= $BASE_URL; ?>assets/img/kaiadmin/logo_light.svg"
                alt="navbar brand"
                class="navbar-brand"
                height="20"
              />
            </a>
            <div class="nav-toggle">
              <button class="btn btn-toggle toggle-sidebar">
                <i class="gg-menu-right"></i>
              </button>
              <button class="btn btn-toggle sidenav-toggler">
                <i class="gg-menu-left"></i>
              </button>
            </div>
            <button class="topbar-toggler more">
              <i class="gg-more-vertical-alt"></i>
            </button>
          </div>
          <!-- End Logo Header -->
        </div>
        <div class="sidebar-wrapper scrollbar scrollbar-inner">
          <div class="sidebar-content">
            <ul class="nav nav-secondary">
              <li class="nav-item <?= $_SERVER['REQUEST_URI'] == '/admin' ? 'active' : '' ?>">
                <a
                  href="<?= $BASE_URL; ?>admin"
                >
                  <i class="fas fa-home"></i>
                  <p>Dashboard</p>
                </a>
              </li>
              <li class="nav-item <?= $_SERVER['REQUEST_URI'] == '/admin/services' ? 'active' : '' ?>">
                <a
                  href="<?= $BASE_URL; ?>admin/services"
                >
                  <i class="fas fa-server"></i>
                  <p>Layanan</p>
                </a>
              </li>
              <li class="nav-item <?= $_SERVER['REQUEST_URI'] == '/admin/orders' ? 'active' : '' ?>">
                <a
                  href="<?= $BASE_URL; ?>admin/orders"
                >
                  <i class="fas fa-money-bill"></i>
                  <p>Transaksi</p>
                </a>
              </li>
            </ul>
          </div>
        </div>
      </div>
      <!-- End Sidebar -->