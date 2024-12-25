import Block from "../Block.js";
import {bannerSelectors} from "./bannerSelectors.js";
import {blockSelectors} from "../blockSelectors.js";
import {blockAssets} from "../blockAssets.js";
import {ImagePreviewInForm} from "../../../imagePreviewInForm/ImagePreviewInForm.js";
import BlockBuilder from "../../BlockBuilder.js";


export default class BannerBlock extends Block {
    inputName;

    container;

    titleInput;

    linkToInput;

    embedInput;

    addButton;

    buttonsContainer;

    buttonsCount = 0;

    insertedImageContainerLandscape;

    insertedImageContainerPortrait;

    initiatorLandscape;

    initiatorPortrait;

    blockBuilder = new BlockBuilder(bannerSelectors.classes.bannerBlockContainer);

    constructor(id, blockName, handlerEventEmitter, data, generateOnConstruct = true) {
        super(id, blockName, handlerEventEmitter, data);
        if(generateOnConstruct) {
            this.generateInputName();
            this.generateView();
        }
    }

    generateInputName() {
        this.inputName = `blocks[${this.getBlockId()}][banner]`;
    }

    getInputName() {
        return this.inputName;
    }

    generateView() {
        [
            this.container,
            this.titleInput,
            this.linkToInput,
            this.embedInput,
            this.buttonsContainer,
            this.addButton
        ] = this.blockBuilder
            .generateInput('Title', this.getTitleInputName())
            .generateInput('Link To', this.getLinkToInputName())
            .generateTextarea('Embed', this.getEmbedInputName())
            .generateBasicContainer(bannerSelectors.classes.buttonsContainer)
            .generateButton(blockSelectors.classes.button, 'Add Button')
            .getSpread();
        this.blockBuilder = null;
        this.container.setAttribute(blockSelectors.attributes.id, this.getBlockId());
        this.generateLandscapeImage();
        this.container.appendChild(this.getLandscapeImageContainer());
        this.generatePortraitImage();
        this.container.appendChild(this.getPortraitImageContainer());
        this.getButtonsContainer().appendChild(this.getAddButton());
        this.container.appendChild(this.getButtonsContainer());
        this.addListeners();
    }

    getTitleInput() {
        return this.titleInput;
    }

    getTitleInputName() {
        return this.getInputName() + '[title]';
    }


    getLinkToInput() {
        return this.linkToInput;
    }

    getLinkToInputName() {
        return this.getInputName() + '[linkTo]';
    }


    getEmbedInput() {
        return this.embedInput;
    }

    getEmbedInputName() {
        return this.getInputName() + '[embed]';
    }

    getButtonsContainer() {
        return this.buttonsContainer;
    }

    addListeners() {
        this.addButtonListener();
    }

    addButtonListener() {
        this.getAddButton().addEventListener('click', () => {
            this.getButtonsContainer().appendChild(this.generateButton());
        });
    }

    generateButton(linkTo = null, text = null) {
        const container = document.createElement('div');
        container.classList.add(bannerSelectors.classes.bannerButtonWrapper);
        const buttonTextInputContainer = this.generateContainerWithLabel('Button Text');
        const buttonTextInput = this.generateInput(this.getButtonTextName());
        buttonTextInputContainer.appendChild(buttonTextInput);

        const buttonLinkToInputContainer = this.generateContainerWithLabel('Button Link To');
        const buttonLinkToInput = this.generateInput(this.getButtonLinkToName());
        buttonLinkToInputContainer.appendChild(buttonLinkToInput);
        const deleteButton = this.generateDeleteButton(container);
        if(linkTo) {
            buttonLinkToInput.value = linkTo;
        }
        if(text) {
            buttonTextInput.value = text;
        }

        container.appendChild(buttonTextInputContainer);
        container.appendChild(buttonLinkToInputContainer);
        container.appendChild(deleteButton);
        this.incrementButtonCount();
        return container;
    }

    getButtonLinkToName() {
        return `${this.getInputName()}[buttons][${this.getButtonsCount()}][linkTo]`
    }

    getButtonTextName() {
        return `${this.getInputName()}[buttons][${this.getButtonsCount()}][text]`
    }

    generateDeleteButton(container) {
        const deleteButton = document.createElement('div');
        deleteButton.classList.add(bannerSelectors.classes.deleteButton);
        deleteButton.innerHTML = blockAssets.deleteIcon;
        deleteButton.title = 'Delete';
        deleteButton.addEventListener('click', () => {
           container.remove();
        });
        return deleteButton;
    }

    getAddButton() {
        return this.addButton;
    }

    generateLandscapeImage() {
        this.landscapeImageContainer = this.generateContainerWithLabel('Landscape Image');
        this.initiatorLandscape = window.mediaLibrary.makeAndMountInitiator(
            false,
            true,
            bannerSelectors.classes.insertImageButtonLandscape,
            'Insert Image');
        this.initiatorLandscape.getInitiatorElement().blockId = this.getBlockId();
        this.landscapeImageContainer.appendChild(this.initiatorLandscape.getInitiatorElement());
    }

    getLandscapeImageContainer() {
        return this.landscapeImageContainer;
    }

    getLandscapeImageInputName() {
        return this.inputName + '[landscapeImage]';
    }

    generatePortraitImage() {
        this.portraitImageContainer = this.generateContainerWithLabel('Portrait Image');
        this.initiatorPortrait = window.mediaLibrary.makeAndMountInitiator(
            false,
            true,
            bannerSelectors.classes.insertImageButtonPortrait,
            'Insert Image');
        this.initiatorPortrait.getInitiatorElement().blockId = this.getBlockId();
        this.portraitImageContainer.appendChild(this.initiatorPortrait.getInitiatorElement());
    }

    getPortraitImageContainer() {
        return this.portraitImageContainer;
    }

    getPortraitImageInputName() {
        return this.inputName + '[portraitImage]';
    }

    addPreviewAndInputLandscape(container) {
        if(this.insertedImageContainerLandscape) {
            this.insertedImageContainerLandscape.remove();
            this.insertedImageContainerLandscape = null;
        }
        this.insertedImageContainerLandscape = container;
        this.getLandscapeImageContainer().insertBefore(this.insertedImageContainerLandscape, this.initiatorLandscape.getInitiatorElement());
    }

    addPreviewAndInputPortrait(container) {
        if(this.insertedImageContainerPortrait) {
            this.insertedImageContainerPortrait.remove();
            this.insertedImageContainerPortrait = null;
        }
        this.insertedImageContainerPortrait = container;
        this.getPortraitImageContainer().insertBefore(this.insertedImageContainerPortrait, this.initiatorPortrait.getInitiatorElement());
    }


    populateWithData() {
        if(this.data) {
            if(this.data.title) {
                this.getTitleInput().value = this.data.title;
            }
            if(this.data.linkTo) {
                this.getLinkToInput().value = this.data.linkTo;
            }
            if(this.data.embed) {
                this.getEmbedInput().value = this.data.embed;
            }
            if(this.data.landscapeImage && this.data.landscapeImage.imageId && this.data.landscapeImage.filename) {
                this.addPreviewAndInputLandscape(
                  ImagePreviewInForm.generate(
                      this.getLandscapeImageInputName(),
                      this.data.landscapeImage.imageId,
                      `/images${this.data.landscapeImage.filename}`
                  ));
            }
            if(this.data.portraitImage && this.data.portraitImage.imageId && this.data.portraitImage.filename) {
                this.addPreviewAndInputPortrait(
                    ImagePreviewInForm.generate(
                        this.getPortraitImageInputName(),
                        this.data.portraitImage.imageId,
                        `/images${this.data.portraitImage.filename}`
                    ));
            }
            if(this.data.buttons) {
                this.data.buttons.forEach((button) => {
                   this.getButtonsContainer().appendChild(this.generateButton(button.linkTo, button.text));
                });
            }
        }
    }

    getButtonsCount() {
        return this.buttonsCount;
    }

    incrementButtonCount() {
        this.buttonsCount++;
    }

    getViewNode() {
        return this.container;
    }

    handleImageSelection(initiatorElement, media) {
        if(media && media[0]) {
            const mediaData = media[0];
            if(initiatorElement.classList.contains(bannerSelectors.classes.insertImageButtonPortrait)) {
                this.addPreviewAndInputPortrait(
                    ImagePreviewInForm.generate(
                        this.getPortraitImageInputName(),
                        mediaData.id,
                        `/images${mediaData.filename}`
                    ));
            } else if (initiatorElement.classList.contains(bannerSelectors.classes.insertImageButtonLandscape)) {
                this.addPreviewAndInputLandscape(
                    ImagePreviewInForm.generate(
                        this.getLandscapeImageInputName(),
                        mediaData.id,
                        `/images${mediaData.filename}`
                    ));
            }
        }
    }
}