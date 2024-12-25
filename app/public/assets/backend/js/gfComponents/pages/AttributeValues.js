import CrudPage from "https://skeletor.greenfriends.systems/dtables/1.x/0.1/src/Page/CrudPage.js";
import {mediaLibraryEvents} from "../mediaLibrary/mediaLibraryEvents.js";
import MediaUploader from "../components/ImageUploader/MediaUploader.js";
import {ImagePreviewInForm} from "../imagePreviewInForm/ImagePreviewInForm.js";
import {pageEvents} from "https://skeletor.greenfriends.systems/dtables/1.x/master/src/Page/pageEvents.js";

export default class AttributeValues extends CrudPage {
    constructor(blockConfig) {
        super();
        this.blockConfig = blockConfig;
        this.modalStyleConfig = {
            create: {
                modalWidth: '80%',
                modalHeight: '80%'
            },
            edit: {
                modalWidth: '80%',
                modalHeight: '80%'
            }
        };
        this.addFormReadyListeners();
        this.addGlobalListeners();
        ImagePreviewInForm.handleFormWithImage(this);
    }

    addGlobalListeners() {

    }

    addFormReadyListeners() {
        this.eventEmitter.on(pageEvents.entityFormReady, (data) => {
            // this.generateSearchForAttributes();
            this.addImageUpload();
            this.getAttributes();
            this.addMediaUploadListener();
            MediaUploader.registerExistingPreviews();
        });
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



    generateSearchForAttributes() {
        let attributeFilter = document.querySelector('.filterWrapper select[name="attributeIdFilter"]');
        let parent = attributeFilter.parentElement;
        let container = document.createElement('div');
        container.classList.add('searchAttributesButtonContainer');
        let searchButton = document.createElement('div');
        searchButton.classList.add('searchAttributesButton');
        searchButton.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="cursor: pointer;">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                  </svg>`
        container.appendChild(searchButton);
        parent.appendChild(container);
        this.addSearchAttributeListener();
    }

    addSearchAttributeListener() {
        let searchButton = document.querySelectorAll('.searchAttributesButton');
        searchButton.forEach((button) => {
            button.addEventListener('click', () => {
                if(document.getElementById('attributeSearchInput')) {
                    return;
                }
                let parent = button.parentElement;
                let selectElement = parent.parentElement.querySelector('select');
                let search = this.generateAttributeSearch();
                parent.appendChild(search);
                search.querySelector('input').focus();
                search.querySelector('.scrollableContainerCustomHeight').appendChild(this.generateAttributeResults(this.attributes, selectElement));
            });
        });
    }

    generateAttributeSearch() {
        let container = document.createElement('div');
        container.classList.add('attributeSearchInputContainer')
        let searchInput = document.createElement('input');
        searchInput.classList.add('form-control');
        searchInput.id = 'attributeSearchInput';

        let resultsContainer = document.createElement('div');
        resultsContainer.classList.add('scrollableContainerCustomHeight');

        let timeout = null;
        searchInput.addEventListener('input', () => {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                this.filterResults(searchInput.value, resultsContainer.querySelectorAll('p'));
            },200)

        });
        container.appendChild(searchInput);
        container.appendChild(resultsContainer);
        return container;
    }

    generateAttributeResults(attributes, selectElement) {
        let wrapper = document.createElement('div');
        attributes.forEach((attribute) => {
            let resultElem = document.createElement('p');
            resultElem.innerText = attribute.attributeName;
            resultElem.setAttribute('id', attribute.attributeId);
            resultElem.addEventListener('click', () => {
                selectElement.value = attribute.attributeId.toString();
                selectElement.dispatchEvent(new Event('change'));
                this.removeAttributeSearch();
            });
            wrapper.appendChild(resultElem);
        });
        return wrapper;
    }
    filterResults(value, targets) {
        targets.forEach((target) => {
            if(this.transliterate(target.innerText).toLowerCase().includes(value.toLowerCase())) {
                target.style.display = 'block';
            } else {
                target.style.display = 'none';
            }
        });
    }

    transliterate(string) {
        return string
            .replace(/ž/g, 'z')
            .replace(/š/g, 's')
            .replace(/đ/g, 'dj')
            .replace(/č/g, 'c')
            .replace(/ć/g, 'c')
            .replace(/Ž/g, 'z')
            .replace(/Š/g, 's')
            .replace(/Đ/g, 'dj')
            .replace(/Č/g, 'c')
            .replace(/Ć/g, 'c');
    }


    removeAttributeSearch() {
        document.getElementById('attributeSearchInput').parentElement.remove();
    }

    async getAttributes() {
        let action = '/attribute/getAttributes/';
        let req = await fetch(action, {
            method: 'POST',
        });
        this.attributes =  JSON.parse(await req.text());
    }

    addImageUpload() {
        let mediaUploader = new MediaUploader();
        mediaUploader.registerNewUploader({
            inputId: 'fileInput',
            targetId: 'imageMediaUpload',
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
                iconContainerClass: 'containerIcon',
                iconPositionRight: '0',
                }
            }
        });
        document.getElementsByClassName('containerIcon')[0].addEventListener('click', () => {
            document.getElementById('fileInput').value = '';
            document.getElementById('preview').innerHTML = '';
        });
    }

}