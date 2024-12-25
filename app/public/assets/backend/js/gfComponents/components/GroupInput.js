export default class GroupInput {
    constructor(props) {
        this.containerId = props.containerId;
        this.container = document.getElementById(this.containerId);
        this.inputId = props.inputId;
        this.input = this.container.querySelector(`#${this.inputId}`);
        this.inputValueName = props.inputValueName;
        this.dummyContainerId = props.dummyContainerId;
        this.dummyContainer = document.getElementById(this.dummyContainerId);
        this.deleteIcon = `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                           </svg>`;
        this.form = props.form;
    }

    init() {
        this.addEnterListenerOnInput();
        this.addRemoveDummyListenerForExistingDummies();
        this.preventSubmitOnEnterIfInputIsFocused();
    }

    preventSubmitOnEnterIfInputIsFocused() {
        this.input.addEventListener('focus', () => {
            this.form.disableEnterSubmit = true;
        });

        this.input.addEventListener('blur', () => {
            this.form.disableEnterSubmit = false;
        });
    }

    addEnterListenerOnInput() {
        this.input.addEventListener('keyup', (e) => {
            if(e.key === 'Enter' && this.input.value.trim().length > 0) {
                this.appendInputAndDummyToContainer();
            }
        });
    }

    appendInputAndDummyToContainer() {
        this.addDummyAndInputToDummyContainer();

        this.clearInputValue();
    }

    addDummyAndInputToDummyContainer() {
        let coupleContainer = document.createElement('div');
        coupleContainer.classList.add('coupleContainer');
        coupleContainer.appendChild(this.generateInput());
        coupleContainer.appendChild(this.generateDummy());
        this.dummyContainer.appendChild(coupleContainer);
    }

    generateDummy() {
        let dummySingleContainer = document.createElement('div');
        dummySingleContainer.classList.add('dummySingleContainer');

        let dummy = document.createElement('span');
        dummy.innerText = this.input.value;
        dummy.classList.add('dummy');

        let deleteDummy = document.createElement('div');
        deleteDummy.title = 'Delete';
        deleteDummy.innerHTML = this.deleteIcon;
        deleteDummy.classList.add('deleteDummy');

        dummySingleContainer.appendChild(dummy);
        dummySingleContainer.appendChild(deleteDummy);

        this.addRemoveDummyListener(deleteDummy);

        return dummySingleContainer;
    }

    addRemoveDummyListener(deleteButton, initialElement = false) {
        deleteButton.addEventListener('click', () => {
            deleteButton.parentElement.parentElement.remove();
        });
    }

    addRemoveDummyListenerForExistingDummies()
    {
        document.querySelectorAll(".deleteDummy:not(.existingGroup)").forEach((dummy) => {
            this.addRemoveDummyListener(dummy, true);
        });
    }

    generateInput() {
        let input = document.createElement('input');
        input.name = `${this.inputValueName}[]`;
        input.value = this.input.value;
        input.type = 'hidden';
        return input;
    }

    clearInputValue() {
        this.input.value = '';
    }


}