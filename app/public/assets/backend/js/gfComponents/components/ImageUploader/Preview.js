export default class Preview {
    /**
     * Preview Constructor
     * @param props
     */
    constructor(props) {

        /**
         * File object.
         */
        this.file = props.file;

        /**
         * Image src.
         */
        this.imgSrc = props.imgSrc;

        /**
         * Preview Container Element.
         * @type {HTMLElement|*}
         */
        this.previewContainerElement = props.previewContainerElement;

        /**
         * Class for the preview image container.
         * @type {string|string|*}
         */
        this.previewImageContainerClass = props.previewImageContainerClass;

        /**
         * Alt attribute value for the image element.
         * @type {string|string|*}
         */
        this.imageAltAttributeValue = props.imageAltAttributeValue;

        /**
         * Remove image object.
         * @type {{iconHeight: string, iconPositionTop: string, iconWidth: string, iconColor: string, html: string, iconPositionLeft: string, iconPositionRight: string, iconPositionBottom: string}|*}
         */
        this.removeImageIcon = props.removeImageIcon;

        /**
         * Input element containing the files.
         * @type {HTMLElement|*}
         */
        this.inputElement = props.inputElement;
    }

    /**
     * Handles all the necessary steps for the preview.
     */
    handlePreview() {
        this.imageContainer =  this.generateImageContainerForPreview();
        this.imageElement = this.generateImage();
        this.hiddenInput = this.generateHiddenInput();
        this.removeImageElement = this.generateRemoveImageElement();
        this.imageContainer.appendChild(this.imageElement);
        this.imageContainer.appendChild(this.removeImageElement);
        this.imageContainer.appendChild(this.hiddenInput);
        this.previewContainerElement.appendChild(this.imageContainer);

        this.addEventListeners();
    }

    generateHiddenInput() {
        let input = document.createElement('input');
        input.type = 'hidden';
        input.name = `newImages[${this.file.name}]`;
        input.value = this.imageContainer.getAttribute('data-position');
        return input;
    }

    /**
     * Add event listeners.
     */
    addEventListeners() {
        this.addRemoveImageListener();
        this.addOnRemoveImageOnHoverListener();
    }


    /**
     * Generates an image container.
     * @returns {HTMLDivElement}
     */
    generateImageContainerForPreview() {
        let container = document.createElement('div');
        container.classList.add(this.previewImageContainerClass);
        container.style.position = 'relative';
        container.setAttribute('data-position', (this.previewContainerElement.querySelectorAll('div').length / 2).toString());
        return container;
    }


    /**
     * Generates an image element with the passed image source in props as the src attribute value.
     * @returns {HTMLImageElement}
     */
    generateImage() {
        let image = document.createElement('img');
        image.src = this.imgSrc;
        image.alt = this.imageAltAttributeValue;
        return image;
    }


    /**
     * Generates the remove image element container with the icon inside.
     * @returns {HTMLDivElement}
     */
    generateRemoveImageElement() {
        let container = document.createElement('div');
        container.style.width = 'max-content';
        container.style.height = 'max-content';
        container.innerHTML = this.removeImageIcon.html;
        this.addStylesToRemoveImageElementContainer(container);
        this.addClassesToRemoveImageElementContainer(container);

        this.icon = container.querySelector('svg');
        this.addStylesToRemoveImageElementIcon();

        return container;
    }

    /**
     * Adds classes from the props if set to the image element container.
     * @param container
     */
    addClassesToRemoveImageElementContainer(container) {
        if(this.removeImageIcon.iconContainerClass) {
            container.classList.add(this.removeImageIcon.iconContainerClass);
        }
    }


    /**
     * Adds styles to the remove icon.
     */
    addStylesToRemoveImageElementIcon() {
        this.icon.style.pointerEvents = 'none';
        this.icon.style.width = this.removeImageIcon.iconWidth;
        this.icon.style.height = this.removeImageIcon.iconHeight;
        this.icon.style.color = this.removeImageIcon.iconColor;
        this.icon.style.backgroundColor = this.removeImageIcon.iconBackgroundColor;
    }


    /**
     * Adds styles to the remove image element container based on the props in the constructor.
     * @param container
     */
    addStylesToRemoveImageElementContainer(container) {
        container.style.position = 'absolute';
        container.style.cursor = 'pointer';
        if(this.removeImageIcon.iconPositionTop) {
            container.style.top = this.removeImageIcon.iconPositionTop;
        }
        if(this.removeImageIcon.iconPositionRight) {
            container.style.right = this.removeImageIcon.iconPositionRight;
        }
        if(this.removeImageIcon.iconPositionLeft) {
            container.style.left = this.removeImageIcon.iconPositionLeft;
        }
        if(this.removeImageIcon.iconPositionBottom) {
            container.style.bottom = this.removeImageIcon.iconPositionBottom;
        }
    }

    /**
     * Registers an event listener when clicking on the remove image icon and calls the removeImage method.
     */
    addRemoveImageListener() {
        this.removeImageElement.addEventListener('click', () => {
           this.removeImage();
        })
    }

    /**
     * Hover event listeners for the remove image container.
     */
    addOnRemoveImageOnHoverListener() {
        this.removeImageElement.addEventListener('mouseover', () => {
            this.toggleClassOnElement(this.removeImageElement, this.removeImageIcon.iconContainerHoverClass);
        })

        this.removeImageElement.addEventListener('mouseleave', () => {
            this.toggleClassOnElement(this.removeImageElement, this.removeImageIcon.iconContainerHoverClass);
        })
    }

    /**
     * Toggles a class for the given element.
     * @param element
     * @param className
     */
    toggleClassOnElement(element, className) {
        if(element.classList.contains(className)) {
            element.classList.remove(className);
            return;
        }
        element.classList.add(className);
    }

    removeImage() {
        this.imageContainer.remove();
        document.querySelectorAll('#galleryPreview > div').forEach((image,index) => {
            image.setAttribute('data-position', index.toString());
            image.querySelector('input').value = index.toString();
        });
    }
}