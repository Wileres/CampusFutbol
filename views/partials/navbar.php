<?php $user=$_SESSION['user_role']??null; $cur=$_GET['p']??'home'; ?>
<nav class="navbar navbar-expand-lg navbar-fc navbar-dark">
    <div class="container">
        <a class="navbar-brand text-white" href="<?= SITE_URL ?>?p=home">
            <i class="bi bi-dribbble me-1"></i><?= htmlspecialchars(SITE_NAME) ?>
        </a>
        <button class="navbar-toggler" type="button"
                data-bs-toggle="collapse" data-bs-target="#navMain"
                aria-controls="navMain" aria-expanded="false" aria-label="Menú">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMain">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?= $cur==='home'?'active':'' ?>" href="<?= SITE_URL ?>?p=home">
                        <i class="bi bi-house-door me-1"></i>Inici
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $cur==='prices'?'active':'' ?>" href="<?= SITE_URL ?>?p=prices">
                        <i class="bi bi-table me-1"></i>Preus
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $cur==='info'?'active':'' ?>" href="<?= SITE_URL ?>?p=info">
                        <i class="bi bi-info-circle me-1"></i>Més informació
                    </a>
                </li>

                <?php if (isLoggedIn() && $user==='parent'): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= $cur==='dashboard'?'active':'' ?>" href="<?= SITE_URL ?>?p=dashboard">
                            <i class="bi bi-grid me-1"></i>Les meves inscripcions
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $cur==='new-reg'?'active':'' ?>" href="<?= SITE_URL ?>?p=new-reg">
                            <i class="bi bi-plus-circle me-1"></i>Nova inscripció
                        </a>
                    </li>
                <?php endif; ?>

                <?php if (isLoggedIn() && $user==='manager'): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= $cur==='manager-weeks'?'active':'' ?>" href="<?= SITE_URL ?>?p=manager-weeks">
                            <i class="bi bi-calendar3 me-1"></i>Setmanes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $cur==='manager-regs'?'active':'' ?>" href="<?= SITE_URL ?>?p=manager-regs">
                            <i class="bi bi-people me-1"></i>Inscripcions
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $cur==='manager-dist'?'active':'' ?>" href="<?= SITE_URL ?>?p=manager-dist">
                            <i class="bi bi-bar-chart me-1"></i>Distribució
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?= in_array($cur,['manager-home','manager-cards','manager-pages','manager-settings','manager-weekly-content'])?' active':'' ?>"
                           href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-pencil-square me-1"></i>Contingut
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item <?= $cur==='manager-home'?'active':'' ?>" href="<?= SITE_URL ?>?p=manager-home">
                                    <i class="bi bi-type me-2"></i>Textos inici
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="<?= SITE_URL ?>?p=manager-cards">
                                    <i class="bi bi-card-text me-2"></i>Targetes inici
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="<?= SITE_URL ?>?p=manager-pages">
                                    <i class="bi bi-file-text me-2"></i>Pàgines
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item <?= $cur==='manager-weekly-content'?'active':'' ?>"
                                   href="<?= SITE_URL ?>?p=manager-weekly-content">
                                    <i class="bi bi-calendar2-heart me-2"></i>Menú &amp; Activitats
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="<?= SITE_URL ?>?p=manager-settings">
                                    <i class="bi bi-gear me-2"></i>Configuració
                                    <?php if (areRegistrationsBlocked()): ?>
                                        <span class="badge bg-danger ms-1" style="font-size:.65rem">Bloquejat</span>
                                    <?php endif; ?>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="<?= SITE_URL ?>?p=manager-test-email">
                                    <i class="bi bi-envelope-check me-2"></i>Diagnòstic correu
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php endif; ?>
            </ul>

            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <?php if (isLoggedIn()): ?>
                    <?php $cu=currentUser(); ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-1"></i>
                            <?= htmlspecialchars($cu['name']) ?>
                            <?php if ($cu['role']==='manager'): ?>
                                <span class="badge bg-warning text-dark ms-1" style="font-size:.65rem">Gestor</span>
                            <?php endif; ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item text-danger" href="<?= SITE_URL ?>?p=logout">
                                    <i class="bi bi-box-arrow-right me-1"></i>Tancar sessió
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link <?= $cur==='login'?'active':'' ?>" href="<?= SITE_URL ?>?p=login">
                            <i class="bi bi-box-arrow-in-right me-1"></i>Iniciar sessió
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $cur==='register'?'active':'' ?>" href="<?= SITE_URL ?>?p=register">
                            <i class="bi bi-person-plus me-1"></i>Registrar-se
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
