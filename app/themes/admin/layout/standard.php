<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?=$this->e($pageTitle)?></title>

    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link href="<?=ADMIN_ASSET_URL .'/vendor/fontawesome-free/css/all.min.css'?>" rel="stylesheet" type="text/css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
          rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="<?=ADMIN_ASSET_URL .'/css/sb-admin-2.min.css'?>" rel="stylesheet">
    <link rel="stylesheet" href="https://skeletor.greenfriends.systems/dtables/1.x/0.0/css/style.css">
    <link href="<?=ADMIN_ASSET_URL .'/css/style.css'?>" rel="stylesheet"/>

    <script src="https://cdn.jsdelivr.net/npm/@tarekraafat/autocomplete.js@10.2.7/dist/autoComplete.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tarekraafat/autocomplete.js@10.2.7/dist/css/autoComplete.min.css">

    <?=$this->section('cssinclude')?>
    <?=$this->section('jsinclude')?>
</head>
<body id="page-top">
<!-- Page Wrapper -->
<div id="wrapper" class="<?=$containerClass ?? ''?>">

    <!-- Sidebar -->
    <?=$this->section('navigation', $this->fetch('partialsGlobal::navigation'))?>
    <!-- End of Sidebar -->

    <!-- Content Wrapper -->
    <div id="content-wrapper" class="d-flex flex-column">
        <!-- Main Content -->
        <div id="content">
            <!-- Topbar -->
            <?=$this->section('topBar', $this->fetch('partialsGlobal::topBar'))?>
            <!-- End of Topbar -->
            <div id="pageMessageContainer">
                <div id="pageErrorContainer"></div>
                <div id="messageContainer"></div>
                <!-- output flash messages -->
                <?php if (isset($messages)) { //@todo remove this, but keep if not ajax?
                    echo $messages;
                } ?>
            </div>
            <!-- Begin Page Content -->
            <div class="container-fluid contentWrapper">
                <?=$this->section('content')?>
            </div>
            <!-- /.container-fluid -->
        </div>
        <!-- End of Main Content -->

        <!-- Footer -->
        <footer class="sticky-footer bg-white">
            <div class="container my-auto">
                <div class="copyright text-center my-auto">
                    <span>Copyright &copy; Konovo <?=date('Y')?>.</span>
                </div>
            </div>
        </footer>
        <!-- End of Footer -->
    </div>
    <!-- End of Content Wrapper -->
</div>
<!-- End of Page Wrapper -->

<!-- Scroll to Top Button-->
<a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
</a>
<div id="userId" data-userId="<?=($loggedIn ?? '')?>"></div>

<!-- Modal -->
<div id="modal" class="hidden">
    <div id="innerModal">
        <div id="errorContainer"></div>
        <div id="modalContent"></div>
        <div id="closeModal">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </div>
    </div>
</div>

<!-- Scroll to Top Button-->
<a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
</a>
<div id="userId" data-userId="<?=($userId ?? '')?>"></div>

<template id="userOptionsTemplate">
    <div id="userOptionsModal">
        <div id="closeUserOptionsButton">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512">
                <path d="M310.6 150.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L160 210.7 54.6 105.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L114.7 256 9.4 361.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L160 301.3 265.4 406.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L205.3 256 310.6 150.6z"></path>
            </svg>
        </div>
        <div id="userColumnOptionsHeader">
            <h4>Column options</h4>
            <div id="userColumnOptionsExpand">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                    <path d="M233.4 406.6c12.5 12.5 32.8 12.5 45.3 0l192-192c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L256 338.7 86.6 169.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3l192 192z"></path>
                </svg>
            </div>
        </div>
        <div id="userColumnOptionsContainer">

        </div>
        <div id="userTableOptionsHeader">
            <h4>Table options</h4>
            <div id="userTableOptionsExpand">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                    <path d="M233.4 406.6c12.5 12.5 32.8 12.5 45.3 0l192-192c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L256 338.7 86.6 169.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3l192 192z"></path>
                </svg>
            </div>
        </div>
        <div id="userTableOptionsContainer">
            <div class="userTableOption">
                <input id="userTableFontSizeOption" type="number" class="userOptionInput" placeholder="Table font size">
            </div>
        </div>
    </div>
</template>
<template id="userColumnOptionTemplate">
    <div class="userColumnOption">
        <div>
            <input type="checkbox" class="toggleColumn">
            <span class="columnName"></span>
        </div>
        <div>
            <input type="number" class="userOptionInput columnWidth" placeholder="Column width">
        </div>
    </div>
</template>

<div id="mediaLibraryModalOverlay">
    <div id="mediaLibraryModalContainer">
        <div id="closeMediaLibraryModalButton">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512">
                <path d="M310.6 150.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L160 210.7 54.6 105.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L114.7 256 9.4 361.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L160 301.3 265.4 406.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L205.3 256 310.6 150.6z"></path>
            </svg>
        </div>
        <div id="mediaLibraryModalTopBar">
            <input multiple type="file" id="mediaUploadInput" class="hidden">
            <div id="uploadMediaButton">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
                    <path d="M246.6 9.4c-12.5-12.5-32.8-12.5-45.3 0l-128 128c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L192 109.3V320c0 17.7 14.3 32 32 32s32-14.3 32-32V109.3l73.4 73.4c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3l-128-128zM64 352c0-17.7-14.3-32-32-32s-32 14.3-32 32v64c0 53 43 96 96 96H352c53 0 96-43 96-96V352c0-17.7-14.3-32-32-32s-32 14.3-32 32v64c0 17.7-14.3 32-32 32H96c-17.7 0-32-14.3-32-32V352z"/>
                </svg>
            </div>
            <input id="mediaLibraryModalSearchInput" class="mediaLibraryInput" placeholder="Search">
            <!--            <select id="mediaLibraryModalDateFilter" class="mediaLibraryInput">-->
            <!--                <option selected disabled value="-1">Filter by date</option>-->
            <!--                <option value="dd.mm.yyy">DD.MM.YYY</option>-->
            <!--            </select>-->
            <button class="btn btn-primary" id="loadMoreMedia"><?=$this->t('Load more')?></button>
        </div>
        <div id="mediaLibraryModalMainContentContainer">
            <div id="mediaLibraryCellsWrapper">
                <div id="mediaLibraryMainCellsContainer"></div>
            </div>
            <div id="mediaLibraryModalSidebar"></div>
        </div>
        <button id="insertMedia" class="mediaLibraryButton hidden">Insert</button>
    </div>
</div>


<!-- Bootstrap core JavaScript-->
<script src="<?=ADMIN_ASSET_URL .'/vendor/jquery/jquery.min.js'?>"></script>
<script src="<?=ADMIN_ASSET_URL .'/vendor/bootstrap/js/bootstrap.bundle.min.js'?>"></script>
<script src="<?=ADMIN_ASSET_URL .'/js/bootstrap-datetimepicker.min.js'?>"></script>

<!-- Core plugin JavaScript-->
<script src="//cdn.quilljs.com/1.3.6/quill.core.js"></script>
<script src="//cdn.quilljs.com/1.3.6/quill.min.js"></script>
<script src="<?=ADMIN_ASSET_URL .'/vendor/jquery-easing/jquery.easing.min.js'?>"></script>
<script src="//cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
<script src="<?=ADMIN_ASSET_URL .'/js/jquery.ui.min.js'?>"></script>
<script src="<?=ADMIN_ASSET_URL .'/js/sortable.min.js'?>"></script>
<script src="<?=ADMIN_ASSET_URL .'/js/accordion.min.js'?>"></script>

<!-- Custom scripts for all pages-->
<script src="<?=ADMIN_ASSET_URL .'/js/sb-admin-2.js'?>"></script>
<script src="<?=ADMIN_ASSET_URL .'/js/global.js'?>" type="module"></script>
</body>
</html>