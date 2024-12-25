export default class Response {
    constructor(response) {
        console.log(response);
        this.response = JSON.parse(response);
    }

    getData() {
        return this.response.data;
    }

    getErrors() {
        return this.response.errors;
    }

    getGeneralErrors() {
        return this.response.errors?.general ?? '';
    }

    getMessage() {
        let generalErrors = '';
        if(this.getErrors() !== '') {
            generalErrors = '\n' + this.getGeneralErrors();
        }
        return this.response.message + generalErrors;
    }

    getStatus() {
        return this.response.status;
    }

    getCSRFTokenInput() {
        return this.response.token;
    }

}