export default class FormField {
    constructor(formField, helper) {
        this.formField = formField;
        this.helper = helper;
        this.type = this.formField.type;
        this.required = this.setRequired(this.formField.getAttribute('data-required'));
        this.requiredText = this.required ? this.formField.getAttribute('data-required-text') : null;
        this.validationStrategy = this.formField.getAttribute('data-validation-strategy') ?? null;
        this.validateStrategyMessage = this.formField.getAttribute('data-validation-strategy-message') ?? null;
        this.selectInputEmptyValue = this.formField.getAttribute('data-select-empty-value') ?? null;
        this.maxLength = parseInt(this.formField.getAttribute('data-max-len'), 10) ?? null;
        this.minLength = parseInt(this.formField.getAttribute('data-min-len'), 10) ?? null;
        this.exactLen = parseInt(this.formField.getAttribute('data-exact-len'), 10) ?? null;
        this.maxLenMessage = this.formField.getAttribute('data-max-len-message') ?? null;
        this.minLenMessage = this.formField.getAttribute('data-min-len-message') ?? null;
        this.exactLenMessage = this.formField.getAttribute('data-exact-len-message') ?? null;
        this.maxNum = parseInt(this.formField.getAttribute('data-max-num'), 10) ?? null;
        this.minNum = parseInt(this.formField.getAttribute('data-min-num'), 10) ?? null;
        this.maxNumMessage = this.formField.getAttribute('data-max-num-message') ?? null;
        this.minNumMessage = this.formField.getAttribute('data-min-num-message') ?? null;

    }


    setRequired(data) {
        return data === 'true';
    }

    validate() {
        if (this.formField.classList.contains('hidden')) {
            return;
        }
        let pattern;
        switch (this.validationStrategy) {
            case 'onlyLetters':
                pattern = /^[\p{L}]*$/u;
                break;
            case 'uppercase':
                pattern = /^[\p{Lu}]*$/u;
                break;
            case 'lowercase':
                pattern = /^[\p{Ll}]*$/u;
                break;
            case 'email':
                pattern = /\S+@\S+\.\S+/;
                break;
            default:
                pattern = this.validationStrategy; // custom pattern
                break;
        }
        if (this.type !== 'select-one') { // non-select inputs
            let formFieldValue = this.formField.value.trim();
            if (formFieldValue.length === 0) { // if empty
                if (this.required) { // if required
                    this.helper.invalidateField(this.formField, this.requiredText);
                    return false;
                }
            }
            if (this.maxLength !== null && this.maxLenMessage !== null && formFieldValue.length > this.maxLength) { // max length string
                this.helper.invalidateField(this.formField, this.maxLenMessage);
                return;
            }
            if (this.minLength !== null && this.minLenMessage !== null && formFieldValue.length < this.minLength) { // min length string
                this.helper.invalidateField(this.formField, this.minLenMessage);
                return;
            }
            if (this.exactLen !== null && this.exactLenMessage !== null && formFieldValue.length !== this.exactLen) { // exact len string
                this.helper.invalidateField(this.formField, this.exactLenMessage);
                return;
            }
            if (this.maxNum !== null && this.maxNumMessage !== null && parseInt(formFieldValue, 10) > this.maxNum) { // max num
                this.helper.invalidateField(this.formField, this.maxNumMessage);
                return;
            }
            if (this.minNum !== null && this.minNumMessage !== null && parseInt(formFieldValue, 10) < this.minNum) { // min num
                this.helper.invalidateField(this.formField, this.minNumMessage);
                return;
            }

            if (pattern !== null && this.validateStrategyMessage !== null) { // validation strategy pattern
                if (!this.validatePattern(pattern)) {
                    this.helper.invalidateField(this.formField, this.validateStrategyMessage);
                    return;
                }
            }
        } else { // select inputs
            let formFieldValue = this.formField.value;
            if (this.required) {
                if (formFieldValue === this.selectInputEmptyValue) { // if the default empty value is the value
                    this.helper.invalidateField(this.formField, this.requiredText);
                    return false;
                }
            }
        }
        this.helper.makeFieldValid(this.formField);
        return true;
    }


    validatePattern(pattern) {
        if (typeof pattern !== 'object') {
            pattern = new RegExp(pattern);
        }
        return pattern.test(this.formField.value);
    }

}
