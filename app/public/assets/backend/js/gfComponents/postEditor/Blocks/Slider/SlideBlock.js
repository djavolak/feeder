import BannerBlock from "../Banner/BannerBlock.js";
import {bannerSelectors} from "../Banner/bannerSelectors.js";
import {blockAssets} from "../blockAssets.js";
import {sliderSelectors} from "./sliderSelectors.js";

export default class SlideBlock extends BannerBlock {
    deleteSlideButton;

    constructor(id, blockName, handlerEventEmitter, data, slideId) {
        super(id, blockName, handlerEventEmitter, data, false);
        this.slideId = slideId;
        this.generateInputName();
        this.generateDeleteSlideButton();
        this.generateView();
    }


    generateView() {
        super.generateView();
        this.getViewNode().appendChild(this.getDeleteSlideButton());
    }

    getSlideId() {
        return this.slideId;
    }

    generateInputName() {
        this.inputName = `blocks[${this.getBlockId()}][slider][slides][${this.getSlideId()}]`;
    }

    generateDeleteSlideButton() {
        this.deleteSlideButton = document.createElement('div');
        this.deleteSlideButton.innerHTML = blockAssets.deleteIcon;
        this.deleteSlideButton.title = 'Delete';
        this.deleteSlideButton.classList.add(sliderSelectors.classes.deleteButton);
        this.deleteSlideButton.addEventListener('click', () => {
            this.getViewNode().remove();
        });
    }

    getDeleteSlideButton() {
        return this.deleteSlideButton;
    }

    generateLandscapeImage() {
        this.landscapeImageContainer = this.generateContainerWithLabel('Landscape Image');
        this.initiatorLandscape = window.mediaLibrary.makeAndMountInitiator(
            false,
            true,
            bannerSelectors.classes.insertImageButtonLandscape,
            'Insert Image');
        this.initiatorLandscape.getInitiatorElement().blockId = this.getBlockId();
        this.initiatorLandscape.getInitiatorElement().slideId = this.getSlideId();
        this.landscapeImageContainer.appendChild(this.initiatorLandscape.getInitiatorElement());
    }

    generatePortraitImage() {
        this.portraitImageContainer = this.generateContainerWithLabel('Portrait Image');
        this.initiatorPortrait = window.mediaLibrary.makeAndMountInitiator(
            false,
            true,
            bannerSelectors.classes.insertImageButtonPortrait,
            'Insert Image');
        this.initiatorPortrait.getInitiatorElement().blockId = this.getBlockId();
        this.initiatorPortrait.getInitiatorElement().slideId = this.getSlideId();
        this.portraitImageContainer.appendChild(this.initiatorPortrait.getInitiatorElement());
    }

}