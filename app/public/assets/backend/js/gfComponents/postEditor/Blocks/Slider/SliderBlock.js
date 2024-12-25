import Block from "../Block.js";
import {sliderSelectors} from "./sliderSelectors.js";
import {blockSelectors} from "../blockSelectors.js";
import SlideBlock from "./SlideBlock.js";

export default class SliderBlock extends Block {
    inputName;

    container;

    addSlideButton;

    slidesContainer;

    nextSlideIndex = 0;

    slides = new Map();

    constructor(id, blockName, handlerEventEmitter, data) {
        super(id, blockName, handlerEventEmitter, data);
        this.generateInputName();
        this.generateView();
    }

    getNextSlideIndex() {
        return this.nextSlideIndex;
    }

    incrementNextSlideIndex() {
        this.nextSlideIndex++;
    }

    getSlides() {
        return this.slides;
    }

    generateInputName() {
        this.inputName = `blocks[${this.getBlockId()}][slider]`;
    }

    getInputName() {
        return this.inputName;
    }

    generateView() {
        this.container = document.createElement('div');
        this.container.classList.add(sliderSelectors.classes.sliderBlockContainer);
        this.generateAddSlideButton();
        this.container.appendChild(this.getAddSlideButton());
        this.generateSlidesContainer();
        this.container.appendChild(this.getSlidesContainer());

    }

    generateAddSlideButton() {
        this.addSlideButton = document.createElement('div');
        this.addSlideButton.classList.add(blockSelectors.classes.button);
        this.addSlideButton.innerText = 'Add Slide';
        this.addSlideListener();
    }

    addSlideListener() {
        this.getAddSlideButton().addEventListener('click', () => {
           this.generateSlide();
        });
    }

    generateSlide(data = null) {
        const slide = new SlideBlock(this.id, this.getBlockName(), this.handlerEventEmitter, data, this.getNextSlideIndex());
        this.slides.set(this.getNextSlideIndex(), slide);
        this.incrementNextSlideIndex();
        this.getSlidesContainer().appendChild(slide.getViewNode());
        return slide;
    }

    getAddSlideButton() {
        return this.addSlideButton;
    }

    generateSlidesContainer() {
        this.slidesContainer = document.createElement('div');
        this.slidesContainer.classList.add(sliderSelectors.classes.slidesContainer);
    }

    getSlidesContainer() {
        return this.slidesContainer;
    }

    getViewNode() {
        return this.container;
    }

    handleImageSelection(initiatorElement, media) {
        const slideId = initiatorElement.slideId;
        if(slideId || slideId === 0) {
            const slide = this.getSlides().get(slideId);
            if(slide) {
                slide.handleImageSelection(initiatorElement, media);
            }
        }
    }

    populateWithData() {
        if(this.data && this.data.slides) {
            this.data.slides.forEach((slide) => {
               const slideObj = this.generateSlide(slide);
               slideObj.populateWithData();
            });
        }
    }
}