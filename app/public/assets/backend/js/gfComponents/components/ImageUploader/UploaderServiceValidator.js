export default class UploaderServiceValidator {
    /**
     * Validates props for Uploader registration and throws errors accordingly
     * @param props
     */
    validatePropsForRegistration(props) {
        /**
         * Validate if required props are set
         */
        if(!props.inputId) {
            this.throwRequiredErrorForUploaderRegistration('inputId');
        }
        if(!props.targetId) {
           this.throwRequiredErrorForUploaderRegistration('targetId');
        }

        /**
         * Input element based on the ID passed in props.
         * @type {HTMLElement}
         */
        let input = document.getElementById(props.inputId);

        /**
         * Target element based on the ID passed in props.
         * @type {HTMLElement}
         */
        let target = document.getElementById(props.targetId);

        /**
         * Validate if elements exist for the required props
         */
        if(!input) {
            this.throwElementIdNotFoundForUploaderRegistration(props.inputId);
        }
        if(!target) {
            this.throwElementIdNotFoundForUploaderRegistration(props.targetId);
        }

        if(!(input instanceof HTMLInputElement)) {
            this.throwNotInstanceOf(props.inputId,'id', 'HTMLInputElement');
        }

        if(props.preview) {
            if(!props.preview.previewContainerId) {
                this.throwRequiredSubFieldForNonRequiredField('preview', 'previewContainerId');
            }

            let previewContainerElement = document.getElementById(props.preview.previewContainerId);
            if(!previewContainerElement) {
                this.throwElementIdNotFoundForUploaderRegistration(props.preview.previewContainerId);
            }

            if(!props.preview.previewImageContainerClass) {
                this.throwRequiredSubFieldForNonRequiredField('preview', 'previewImageContainerClass');
            }

            if(!props.preview.imageAltAttributeValue) {
                this.throwRequiredSubFieldForNonRequiredField('preview', 'imageAltAttributeValue');
            }

            if(!props.preview.removeImageIcon) {
                this.throwRequiredSubFieldForNonRequiredField('preview', 'removeImageIcon');
            }
            if(!props.preview.removeImageIcon.html) {
                this.throwRequiredSubFieldForNonRequiredField('removeImageIcon', 'html');
            }

        } else {
            this.throwRequiredErrorForUploaderRegistration('preview');
        }


    }

    /**
     * Throws an error which states that the passed field is required when registering a new uploader.
     * @param field {String}
     * @param subField {String}
     */
    throwRequiredErrorForUploaderRegistration(field, subField = null) {
        if(subField) {
            throw new Error(`${field} is required in ${subField} when registering a new uploader`);
        }
        throw new Error(`${field} is required when registering a new uploader`);
    }

    /**
     * Throws an error which states that the element with the passed ID was not found.
     * @param id
     */
    throwElementIdNotFoundForUploaderRegistration(id) {
        throw new Error(`Element with the id of ${id} was not found`);
    }

    /**
     * Throws en error which states that the element with the passed class/id is not of the passed type.
     * @param selector {String} id/class of the element
     * @param reference {String} "id" or "class"
     * @param type {String}
     */
    throwNotInstanceOf(selector, reference, type) {
       throw new Error(`Element with the ${reference} ${selector} is not of ${type} type`);
    }

    /**
     * Throws an error which states that the subfield is required if using the field.
     * @param field {String}
     * @param subfield {String}
     */
    throwRequiredSubFieldForNonRequiredField(field, subfield) {
        throw new Error(`${subfield} is required whilst using ${field} when registering a new uploader`)
    }
}