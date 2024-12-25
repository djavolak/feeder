import {imagePreviewInFormSelectors} from "./imagePreviewInFormSelectors.js";
import {initiatorSelectors} from "../mediaLibrary/Initiator/initiatorSelectors.js";
import {pageEvents} from "https://skeletor.greenfriends.systems/dtables/1.x/master/src/Page/pageEvents.js";
import {mediaLibraryEvents} from "../mediaLibrary/mediaLibraryEvents.js";

export class ImagePreviewInForm {
    static inputNameAttribute = 'data-input-name';

    static generate(inputName, inputValue, previewSrc) {
        return this.generateContainer(inputName, inputValue, previewSrc);
    }

    static generateContainer(inputName, inputValue, previewSrc) {
        const container = document.createElement('div');
        container.classList.add(imagePreviewInFormSelectors.classes.container);
        container.appendChild(this.generateInput(inputName, inputValue));
        container.appendChild(this.generatePreview(previewSrc));
        container.appendChild(this.generateRemoveButton(container));
        return container;
    }

    static generateInput(inputName, inputValue) {
        const input = document.createElement('input');
        input.name = inputName;
        input.value = inputValue;
        input.type = 'hidden';
        return input;
    }

    static generatePreview(src) {
        const preview = document.createElement('img');
        preview.src = src;
        return preview;
    }

    static generateRemoveButton(container) {
        const removeButton = document.createElement('div');
        removeButton.classList.add(imagePreviewInFormSelectors.classes.removeButton);
        removeButton.innerText = 'Remove';
        removeButton.addEventListener('click', () => {
           container.remove();
        });
        return removeButton;
    }

    static handleFormWithImage(page)
    {
        this.handleExisting(page);
        this.handleOnMediaInsert(page);
    }

    static handleExisting(page)
    {
        page.eventEmitter.on(pageEvents.entityFormReady, (data) => {
            const form = page.modal.getFormInModal();
            if(form) {
                const initiators = form.querySelectorAll(`.${initiatorSelectors.classes.mediaLibraryInitiator}`);
                if(initiators.length > 0) {
                    initiators.forEach((initiator) => {
                        const initiatorInstance = window.mediaLibrary.mountSingleInitiator(initiator);
                        if(initiatorInstance && initiator.parentElement && initiator.parentElement.classList.contains('mediaUpload')
                            && data && data.action && data.action === 'edit') {
                            const removeImageButton =
                                initiator.parentElement.querySelector(`.${imagePreviewInFormSelectors.classes.removeButton}`);
                            if(removeImageButton) {
                                removeImageButton.addEventListener('click', () => {
                                    removeImageButton.parentElement.remove();
                                });
                            }
                        }
                    });
                }
            }
        });
    }

    static handleOnMediaInsert(page) {
        const mediaLibraryEmitter = window.mediaLibrary.eventEmitter;
        mediaLibraryEmitter.on(mediaLibraryEvents.mediaReadyToInsert, (data) => {
            if(data.initiatorElement && data.initiatorElement.parentElement) {
                const mediaData = data.media;
                const container = data.initiatorElement.parentElement;
                if(container && container.classList.contains('mediaUpload') && this.validateMedia(mediaData)) {
                    const inputName = container.getAttribute(this.inputNameAttribute);
                    this.removeInsertedImage(page, container);
                    container.insertBefore(
                        this.generate(
                            inputName,
                            mediaData[0].id,
                            `/images${mediaData[0].filename}`)
                        , data.initiatorElement);
                }
            }
        })
    }

    static removeInsertedImage(page, container) {
        if(container) {
            const imagePreviewContainer = container.querySelector(`.${imagePreviewInFormSelectors.classes.container}`);
            if(imagePreviewContainer) {
                imagePreviewContainer.remove();
            }
        }
    }

    static validateMedia(mediaData) {
        let valid = false;
        if(mediaData && mediaData[0] && mediaData[0].id && mediaData[0].filename) {
            valid = true;
        }
        return valid;
    }

    static getImageSrcOnFileSelect(input)
    {
        const imageFiles = input.files;
        const imageFilesLength = imageFiles.length;
        if (imageFilesLength > 0) {
            return URL.createObjectURL(imageFiles[0]);
        }
        return null;
    }
}