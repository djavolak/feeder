import Uploader from "./Uploader.js";
import UploaderServiceValidator from "./UploaderServiceValidator.js";

export default class MediaUploader {

    /**
     * MediaUploader Constructor
     */
    constructor() {
        /**
         * @type {UploaderServiceValidator}
         */
        this.service = new UploaderServiceValidator();
        /**
         * @type {[Uploader]}
         */
        this.uploaders = [];
    }




    /**
     * Validate props for a new uploader and register it if the validation passes.
     * @param props
     * @return {Uploader}
     */
    registerNewUploader(props) {
        try {
            this.service.validatePropsForRegistration(props);
            let uploader = new Uploader(props);
            uploader.addEventListeners();
            uploader.addStyles();
            this.uploaders.push(uploader);
            return uploader;
        } catch(e) {
            console.error(e);
        }
    }

    static registerExistingPreviews() {
        const addDeleteListener = (preview) => {
            let removeElement = preview.querySelector('.containerIcon');
            preview.style.position = 'relative';
            removeElement.addEventListener('click', () => {
                generateInputForDeletedImages(preview);
                preview.remove();
                document.querySelectorAll('#galleryPreview > div').forEach((image,index) => {
                    image.setAttribute('data-position', index.toString());
                    image.querySelector('input').value = index.toString();
                });
            });
        };
        const addStyleToRemoveElement = (container) => {
            container.style.position = 'absolute';
            container.style.cursor = 'pointer';
            container.style.top = '0';
            container.style.right = '0';
        }

        const generateInputForDeletedImages = (preview) => {
            let imageId = preview.getAttribute('data-image-id');
            preview.parentElement.appendChild(generateDeletedInput(imageId));
        };

        const generateDeletedInput = (value) => {
          let input = document.createElement('input');
          input.name = 'deletedImages[]';
          input.type = 'hidden';
          input.value = value;
          return input;
        };

        let existingPreviews = document.querySelectorAll('.previewImage') ?? null;
        if(existingPreviews) {
            existingPreviews.forEach((preview) => {
                addStyleToRemoveElement(preview.querySelector('.containerIcon'));
                addDeleteListener(preview);
            });
        }
    }

}