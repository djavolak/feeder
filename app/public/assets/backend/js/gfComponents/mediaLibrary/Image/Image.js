export default class Image {
    imagePath = '/images/';
    ignoredFormDataFields = ['createdAt', 'updatedAt', 'img'];
    constructor(data) {
        this.data = this.parseData(data);
    }

    getFilename() {
        return this.data.filename;
    }

    getSrc() {
        return `${this.imagePath}${this.getFilename()}`;
    }

    getId() {
        return this.data.id;
    }

    getAlt() {
        return this.data.alt;
    }

    getLabel() {
        return this.data.label;
    }

    getAuthor() {
        return this.data.author;
    }

    parseData(data) {
        Object.keys(data).forEach((key) => {
            if(!data[key]) {
                return;
            }
            if (typeof data[key] === 'object' && data[key].value) {
                data[key] = data[key].value;
            }
       });
       return data;
    }

    getFormData() {
        const formData = new FormData();
        Object.keys(this.data).forEach((key) => {
           if(!this.ignoredFormDataFields.includes(key)) {
               formData.append(key, this.data[key]);
           }
        });
        return formData;
    }
}