import FormField from "./FormField.js";
import FormValidatorHelper from "./FormValidatorHelper.js";

export default class FormValidator {
    constructor(props) {
        this.form = props.form;
        this.submitButton = this.form ?  this.form.querySelector('input[type="submit"]') : null;
        this.formFieldClassNames = props.formFieldClassNames;
        this.inputs = this.form ?  this.form.querySelectorAll(`.${this.formFieldClassNames}`) : '';
        this.formScrollableContainer = props.formScrollableContainer ?? window;
        this.helper = new FormValidatorHelper();
        this.formFields = [];
    }

    init() {
        this.registerFormFields();
        this.addSubmitListener();
    }

    addSubmitListener() {
        if(this.submitButton) {
            this.submitButton.addEventListener('click', (e) => {
                if (!this.validate()) {
                    e.stopImmediatePropagation();
                    e.preventDefault();
                    this.scrollToFirstInvalidFormField();
                }
            });
        }
    }

    registerFormFields() {
        if(this.inputs) {
            this.inputs.forEach((field) => {
                let formField = new FormField(field, this.helper);
                this.formFields.push(formField);
            });
        }
    }

    validate() {
        let valid = true;
        this.formFields.forEach((field) => {
            if(!field.validate()) {
              valid = false;
            }
        })
        return valid;
    }

    scrollToFirstInvalidFormField() {
        let firstInvalidFormField = this.form.querySelector(`.${this.helper.getInvalidFormFieldClassName()}`);
        if(firstInvalidFormField) {
            this.helper.scrollTo(firstInvalidFormField,this.formScrollableContainer);
        }
    }


}