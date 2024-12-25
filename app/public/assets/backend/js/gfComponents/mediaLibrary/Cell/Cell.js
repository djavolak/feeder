import {cellSelectors} from "./cellSelectors.js";
import {cellEvents} from "./cellEvents.js";
import Loader from "https://skeletor.greenfriends.systems/dtables/1.x/master/src/Loader/Loader.js";
import Image from "../Image/Image.js";

export default class Cell {
    selected = false;

    previewElement = document.createElement('img');

    constructor(props) {
        this.mediaData = props.mediaData;
        this.image = new Image(this.mediaData);
        this.eventEmitter = props.eventEmitter;
        this.generateView();
        this.addListeners();
    }

    static generateDummyCell() {
        let dummyContainer = document.createElement('div');
        dummyContainer.classList.add(cellSelectors.classes.container);
        dummyContainer.classList.add(cellSelectors.classes.dummyContainer);
        let loader = new Loader();
        loader.start(dummyContainer);
        return dummyContainer;
    }

    static getLastDummyCell() {
        let dummyCells = document.querySelectorAll(`.${cellSelectors.classes.dummyContainer}`);
        let length = dummyCells.length;
        if(length > 0) {
            return dummyCells[length-1];
        }
        return null;
    }

    generateView() {
        this.container = document.createElement('div');
        this.container.classList.add(cellSelectors.classes.container);
        this.setPreviewSrc();
        this.container.appendChild(this.previewElement);
    }


    setPreviewSrc() {
        this.previewElement.src = this.image.getSrc();
    }

    getImage() {
        return this.image;
    }

    getView() {
        return this.container;
    }

    addListeners() {
        this.container.addEventListener('click', () => {
            this.toggleSelect();
        });
    }

    toggleSelect() {
        this.selected = this.container.classList.toggle(cellSelectors.classes.selected);
        this.eventEmitter.emit(cellEvents.selectToggled, {
            cell: this
        })
        return this.selected;
    }

    deselectWithoutEmitting() {
        this.selected = false;
        this.container.classList.remove(cellSelectors.classes.selected);
    }

    getMediaData() {
        return this.mediaData;
    }

    isSelected() {
        return this.selected;
    }

    destroy() {
        this.container.remove();
    }

    update(mediaData) {
        this.mediaData = mediaData;
        this.image = new Image(this.mediaData);
    }
}
