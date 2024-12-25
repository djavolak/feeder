import CrudPage from "https://skeletor.greenfriends.systems/dtables/1.x/0.1/src/Page/CrudPage.js";
import {mediaLibraryEvents} from "../mediaLibrary/mediaLibraryEvents.js";
import MediaUploader from "../components/ImageUploader/MediaUploader.js";
import {ImagePreviewInForm} from "../imagePreviewInForm/ImagePreviewInForm.js";
import PostEditor from "../postEditor/PostEditor.js";
import {pageEvents} from "https://skeletor.greenfriends.systems/dtables/1.x/master/src/Page/pageEvents.js";
import ValuesGenerator from "../valuesGenerator/ValuesGenerator.js";
import CategoryTree from "../categoryTree/CategoryTree.js";

export default class Category extends CrudPage {
    constructor(blockConfig) {
        super();
        this.blockConfig = blockConfig;
        this.modalStyleConfig = {
            create: {
                modalWidth: '80%',
                modalHeight: '60%'
            },
            edit: {
                modalWidth: '80%',
                modalHeight: '60%'
            }
        };
        this.dataTableOptions = {
            enableCheckboxes: true,
            shiftCheckboxModifier: true
        };
        this.addAdditionalListeners();
        this.addGlobalListeners();
        this.addFormReadyListeners();
        this.addSyncAction();
        ImagePreviewInForm.handleFormWithImage(this);
    }

    addFormReadyListeners() {
        this.eventEmitter.on(pageEvents.entityFormReady, (data) => {
            this.initPostEditor();
            if(data && data.action && data.action === 'edit') {
                const blockData = this.getBlockData();
                if(blockData) {
                    this.postEditor.preloadBlocks(blockData);
                }
            }
            this.loadValueGenerators();
            this.addTenantSelectListener();
            // this.addDataTypeButtonsListeners();
            // const categoryTree = new CategoryTree();
            // categoryTree.init();
        });
    }

    initPostEditor() {
        this.postEditorContainer = document.getElementById('contentContainer');
        if(this.postEditorContainer) {
            this.postEditor = new PostEditor(this.postEditorContainer, [], this.blockConfig);
            this.postEditor.init();
        }
    }

    getBlockData() {
        const blockDataElem = document.querySelector('.blockData');
        if(blockDataElem && blockDataElem.getAttribute('data-blocks')) {
            return JSON.parse(blockDataElem.getAttribute('data-blocks'));
        }
        return null;
    }

    loadValueGenerators() {
        const form = this.modal.getFormInModal();
        if(form) {
            let valuesGeneratorContainers = ValuesGenerator.getAllContainersForInit(form);
            if(valuesGeneratorContainers) {
                valuesGeneratorContainers.forEach((container) => {
                    const valuesGenerator = new ValuesGenerator(container, form);
                    valuesGenerator.init();
                });
            }
        }
    }

    addSyncAction() {
        this.dataTableActions.sync = {
            order:3,
            content: 'sync',
            className: '',
            callback: async (entity) => {

            }
        }
    }

    addGlobalListeners() {
        this.addMediaInsertedListener();
    }

    addAdditionalListeners() {
        this.addSyncButtonEventListener();
        document.body.addEventListener(this.formReadyEventName, () => {
            this.addTextEditorListener();
            this.addMediaUploadListener();

            MediaUploader.registerExistingPreviews();
        });
    }

    addMediaInsertedListener() {
        const mediaLibraryEmitter = window.mediaLibrary.eventEmitter;
        mediaLibraryEmitter.on(mediaLibraryEvents.mediaReadyToInsert, (data) => {
            if (data.initiatorElement && this.postEditor && this.postEditor.getBlocksContainer().contains(data.initiatorElement)) {
                // Image block
                const blockId = data.initiatorElement.blockId;
                if(blockId || blockId === 0) {
                    const block = this.postEditor.getActiveBlockById(blockId);
                    if(typeof block.handleImageSelection === 'function') {
                        block.handleImageSelection(data.initiatorElement, data.media);
                    }
                }
            }
        });
    }

    addImageUploadHandler() {
        let mediaUploader = new MediaUploader();
        mediaUploader.registerNewUploader({
            inputId: 'fileInput',
            targetId: 'featuredMediaUpload',
            triggerCustomEvents: false,
            appearance: {
                windowOnDragTargetClass: 'windowOnDragTargetClass',
                onTargetDragOverClass: 'onTargetDragOverClass'
            },
            preview: {
                previewContainerId: 'preview',
                previewImageContainerClass: 'previewImage',
                imageAltAttributeValue: 'image preview',
                removeImageIcon: {
                    html: `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>`,
                    iconWidth: '32px',
                    iconHeight: '32px',
                    iconColor: 'red',
                    iconPositionTop: '0',
                    iconPositionRight: '0',
                }
            }
        });
    }

    addTextEditorListener() {
        tinymce.remove();
        this.startTextEditor('description');
        this.startTextEditor('secondDescription');
    }

    addMediaUploadListener() {
        let mediaUploader = new MediaUploader();
        mediaUploader.registerNewUploader({
            inputId: 'fileInput',
            targetId: 'mediaUpload',
            triggerCustomEvents: false,
            appearance: {
                windowOnDragTargetClass: 'windowOnDragTargetClass',
                onTargetDragOverClass: 'onTargetDragOverClass'
            },
            preview: {
                previewContainerId: 'preview',
                previewImageContainerClass: 'previewImage',
                imageAltAttributeValue: 'image preview',
                removeImageIcon: {
                    html: `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>`,
                    iconWidth: '32px',
                    iconHeight: '32px',
                    iconColor: 'red',
                    iconPositionTop: '0',
                    iconPositionRight: '0',
                    iconPositionBottom: '0',
                    iconPositionLeft: '0',
                    iconContainerClass: 'containerIcon',
                    iconContainerHoverClass: 'iconHovered'
                }
            }
        });
    }
    startTextEditor(selector) {
        let element = document.getElementById(selector);
        if (element) {
            let toolbarOptions = 'styleselect | bold italic | alignleft aligncenter alignright alignjustify | outdent indent | tables';
            tinymce.init({
                selector: '#' + selector,
                plugins: 'table',
                toolbar: toolbarOptions,
                setup:function(ed) {
                    ed.on('change', function(e) {
                        document.getElementById(`${selector}Input`).value = ed.getContent();
                    });
                }
            });
        }
    }

    addTenantSelectListener() {
        let tenantSelect = document.getElementById('tenantSelect');
        tenantSelect.addEventListener('change', async () => {
            let value = tenantSelect.value;
            let data = [];
            let action = tenantSelect.getAttribute('data-action') + value + '/';
            const req = await fetch(action);
            data = await req.json();
            let categorySelect = document.getElementById('categorySelect');
            categorySelect.innerText = null;
            categorySelect.appendChild(this.generateCategorySelectOption({id: 0, title:'No parent'}))
            let currentCategoryId = categorySelect.getAttribute('data-category-id');
            data.forEach((category) => {
                console.log(category);
                if(currentCategoryId === category.id.toString()) {
                    return;
                }
                categorySelect.appendChild(this.generateCategorySelectOption(category));
            })
        })
    }

    generateCategorySelectOption(data)
    {
        let option = document.createElement('option');
        option.value = data.id;
        option.innerText = data.title;
        return option;
    }

    addSyncButtonEventListener() {
        // document.body.addEventListener(this.crudTable.tablePopulatedEventName, () => {
        //     let syncButtons = document.querySelectorAll('.categorySyncButton');
        //     syncButtons.forEach((button) => {
        //         button.addEventListener('click', async () => {
        //             let action = button.getAttribute('data-action');
        //             button.setAttribute('disabled', 'disabled');
        //             let data = JSON.parse(await this.fetcher.fetchData(action, 'GET'));
        //             if (data.status === 'ok') {
        //                 alert('Sync started');
        //                 button.removeAttribute('disabled');
        //             }
        //         })
        //     })
        // });
    }
}