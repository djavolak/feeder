import CrudPage from "https://skeletor.greenfriends.systems/dtables/1.x/0.1/src/Page/CrudPage.js";
import {mediaLibraryEvents} from "../mediaLibrary/mediaLibraryEvents.js";
import MediaUploader from "../components/ImageUploader/MediaUploader.js";
import {ImagePreviewInForm} from "../imagePreviewInForm/ImagePreviewInForm.js";

export default class Tag extends CrudPage {
    constructor() {
        super();
        this.defaultSort = [
            {order: 'ASC', orderBy: 'title'},
        ];
        this.addAdditionalListeners();
        this.addGlobalListeners();
        ImagePreviewInForm.handleFormWithImage(this);
    }

    addGlobalListeners() {
        this.addMediaInsertedListener();
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

    addAdditionalListeners() {
        document.body.addEventListener(this.formReadyEventName, () => {
            this.addStickerOptionListener();
            this.addImageUploadHandler();
            MediaUploader.registerExistingPreviews();
        });
    }


    addStickerOptionListener() {
        let stickerOptionSelect = document.getElementById('stickerOption');
        stickerOptionSelect.addEventListener('change', () => {
            this.toggleStickerOptions(stickerOptionSelect);
        });
    }

    toggleStickerOptions(stickerOptionSelect) {
        let stickerText = document.getElementById('stickerLabelContainer');
        let stickerImage = document.getElementById('stickerImageContainer');
        if(stickerOptionSelect.value === '0') {
            stickerText.classList.remove('hidden');
            stickerImage.classList.add('hidden');
            stickerImage.querySelector('#fileInput').value = '';
            stickerImage.querySelector('#preview').innerHTML = '';
            return;
        }
        stickerText.classList.add('hidden');
        stickerText.querySelector('#stickerLabel').value = '';
        stickerImage.classList.remove('hidden');
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


}