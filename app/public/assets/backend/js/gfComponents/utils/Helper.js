export default class Helper {

    constructor() {
        this.modal = document.getElementById('modal');
        this.innerModal = document.getElementById('innerModal');
        this.modalContent = document.getElementById('modalContent');
        this.closeModalButton = document.getElementById('closeModal');
        this.messageContainer = document.getElementById('messageContainer');
        this.errorContainer = document.getElementById('errorContainer');
        this.pageErrorContainer = document.getElementById('pageErrorContainer');
        this.body = document.body;
        if(this.closeModalButton) {
            this.addCloseModalListener();
        }
        this.loaderId = 'loader';
    }

    getEntityName() {
        return document.getElementById('crudTable').getAttribute('data-js-page').toLowerCase();
    }

    /**
     *
     * @return {HTMLElement|null}
     */
    getModal() {
        if(this.modal) {
            return this.modal;
        }
        return null;
    }

    /**
     *
     * @return {HTMLElement|null}
     */
    getInnerModal() {
        if(this.innerModal) {
            return this.innerModal;
        }
        return null;
    }

    /**
     * @param width
     * @param height
     */
    openModal(width, height) {
        if(this.modal && this.innerModal) {
            this.innerModal.style.width = width;
            this.innerModal.style.height = height;
            this.modal.classList.remove('hidden');
            this.body.classList.add('freeze');
        }
    }

    closeModal() {
        if(this.modal && this.innerModal && this.modalContent) {
            this.innerModal.style.width = '';
            this.innerModal.style.height = '';
            this.modalContent.innerHTML = '';
            this.modal.classList.add('hidden');
            this.body.classList.remove('freeze');
            this.removeErrorMessages();
        }
    }

    /**
     * @param content
     */
    populateModal(content) {
        if(this.modal && this.modalContent) {
            this.modalContent.innerHTML = content;
        }
    }

    /**
     * @param container
     * @param content
     */
    populateContainer(container, content) {
        if(container) {
            container.innerHTML = content;
        }
    }

    addCloseModalListener() {
        this.closeModalButton.addEventListener('click', () => {
            this.closeModal();
        });
    }

    /**
     * @param container
     * @param loaderSize
     */
    startLoader(container, loaderSize) {
        if(!document.getElementById(`${this.loaderId}`)) {
            container.appendChild(this.generateLoader(loaderSize));
        }
    }

    stopLoader() {
        let loader = document.getElementById(`${this.loaderId}`);
        if(loader) {
            loader.remove();
        }
    }

    /**
     * @param loaderSize
     * @return {HTMLDivElement}
     */
    generateLoader(loaderSize) {
        let loader = document.createElement('div');
        loader.id = this.loaderId;
        loader.classList.add(loaderSize);
        return loader;
    }

    removeErrorsFromPage() {
        this.pageErrorContainer.innerHTML = '';
    }

    printMessage(status, message) {
        this.removeClassesFromMessageContainer();
        this.pageErrorContainer.innerText = '';
        this.messageContainer.innerText = '';
        this.addClassToMessageContainerByStatus(status);
        this.messageContainer.innerText = message;
    }

    addClassToMessageContainerByStatus(status) {
        this.messageContainer.classList.add('alert')
        if(status) {
            this.messageContainer.classList.add('alert-success');
            return;
        }
        this.messageContainer.classList.add('alert-danger');
    }
    removeClassesFromMessageContainer() {
        this.messageContainer.classList.remove('alert');
        this.messageContainer.classList.remove('alert-danger');
        this.messageContainer.classList.remove('alert-success');
    }


    getCurrentDateTimeString() { // @todo remove this and use date created and updated from the return object data
        let currentDate = new Date();
        return `${currentDate.getDate()}/${currentDate.getMonth()+1}/${currentDate.getFullYear()} ${currentDate.getHours()}:${currentDate.getMinutes()}`;
    }

    /**
     * @param elem
     * @param loaderSize
     */
    startLoaderInElementParent(elem, loaderSize) {
        elem.parentElement.style.position = 'relative';
        this.startLoader(elem.parentElement, loaderSize);
        elem.style.visibility = 'hidden';
    }

    makeElementVisibleAfterLoader(elem) {
        elem.style.visibility = 'visible';
    }

    printErrors(errors, printInModal = true) {
        this.messageContainer.innerText = '';
        this.removeClassesFromMessageContainer();
        let container = this.errorContainer;
        if(!printInModal) {
            container = this.pageErrorContainer;
        }
        container.innerHTML = '';
        let wrapper = document.createElement('div');
        errors.forEach((error) => {
            let errorElem = document.createElement('p');
            errorElem.classList.add('alert-danger', 'alert');
            errorElem.innerText = error;
            wrapper.appendChild(errorElem);
        });
        container.appendChild(wrapper);
    }

    removeErrorMessages() {
        this.errorContainer.innerHTML = '';
    }

    scrollToTopOfModal() {
        this.getInnerModal().scroll({top:0, behavior:'smooth'});
    }

    scrollToTopOfWindow() {
        window.scroll({top:0, behavior:'smooth'});
    }

    updateCSRFToken(tokenInput) {
        let form = this.getFormInModal() ?? this.getForm();
        if(form) {
            form.querySelector('[name^="_csrf_token"]').remove();
            form.insertAdjacentHTML('beforeend', tokenInput);
        }
    }

    getFormInModal() {
        return this.getModal().querySelector('form') ?? null;
    }

    getForm() {
        return document.querySelector('form') ?? null;
    }

}