import Preview from "./Preview.js";

export default class Uploader {

    /**
     * Uploader Constructor
     * @param props
     */

    constructor(props) {
        /**
         * ID of the file input
         * @type {string|*}
         */
        this.inputId = props.inputId;

        /**
         * ID of the target element
         * @type {string|*}
         */
        this.targetId = props.targetId;

        /**
         *
         * @type {{previewImageContainerClass: string, imageAltAttributeValue: string, previewContainerId: string, removeImageIcon: {iconHeight: string, iconPositionTop: string, iconWidth: string, iconColor: string, html: string, iconPositionLeft: string, iconPositionRight: string, iconPositionBottom: string}}|*}
         */
        this.preview = props.preview;

        /**
         * Preview container ID.
         * @type {string|*}
         */
        this.previewContainerId = this.preview.previewContainerId;

        /**
         * Preview Container Element based on the preview container ID.
         * @type {HTMLElement}
         */
        this.previewContainerElement = document.getElementById(this.previewContainerId);

        /**
         * Class for the preview image container.
         * @type {string}
         */
        this.previewImageContainerClass = props.preview.previewImageContainerClass;

        /**
         * Image alt attribute to be used for all images in the preview section.
         * @type {string}
         */
        this.imageAltAttributeValue = props.preview.imageAltAttributeValue;

        /**
         * Remove image object
         * @type {{iconHeight: string, iconPositionTop: string, iconWidth: string, iconColor: string, html: string, iconPositionLeft: string, iconPositionRight: string, iconPositionBottom: string}}
         */
        this.removeImageIcon = props.preview.removeImageIcon;

        /**
         * Input Element based on the inputId provided in the props.
         * @type {HTMLElement}
         */
        this.inputElement = document.getElementById(this.inputId);
        /**
         * Target Element based on the inputId provided in the props.
         * @type {HTMLElement}
         */
        this.targetElement = document.getElementById(this.targetId);

        /**
         * Set optional fields based on the props.
         */
        this.setOptionalFields(props);

        /**
         * Is there a file being dragged in the window.
         * @type {boolean}
         */
        this.isDragging = false;

        this.previews = [];

        if(this.triggerCustomEvents) {
            this.setCustomEvents();
        }
    }


    /**
     * Sets optional fields based on the props passed.
     * @param props
     */
    setOptionalFields(props) {
        if(props.appearance) {
            if(props.appearance.windowOnDragTargetClass) {
                this.windowOnDragTargetClass = props.appearance.windowOnDragTargetClass;
            }
            if(props.appearance.onTargetDragOverClass) {
                this.onTargetDragOverClass = props.appearance.onTargetDragOverClass;
            }
        }
        if(props.triggerCustomEvents) {
            this.triggerCustomEvents = true;
        }
    }

    /**
     * Sets custom events for dragging, stop dragging, dropped.
     */
    setCustomEvents() {
        this.draggingEvent = new CustomEvent('dragging', {
            detail: {
                info: this.getInfoForCustomEvent('dragging')
            }
        });
        this.draggingStoppedEvent = new CustomEvent('draggingStopped', {
            detail: {
                info: this.getInfoForCustomEvent('draggingStopped')
            },
        });
        this.droppedEvent = new CustomEvent('dropped', {
            detail: {
                info: this.getInfoForCustomEvent('dropped')
            }
        });
    }

    getInfoForCustomEvent(event) {
        return {
            event: event,
            mediaUploaderTargetElement: this.targetElement,
            mediaUploaderInputElement: this.inputElement,
            mediaUploaderPreviewContainerElement: this.previewContainerElement
        };
    }

    /**
     * Calls all methods that add event listeners
     */
    addEventListeners() {
        this.addHandleDroppedMediaListener();
        this.addStylesOnDragDrop();
    }

    /**
     * Add necessary styles to the input and the target element.
     */
    addStyles() {
        this.inputElement.style.opacity = '0';
        this.inputElement.style.position = 'absolute';
        this.inputElement.style.width = '100%';
        this.inputElement.style.height = '100%';
        this.inputElement.style.cursor = 'pointer';

        this.targetElement.style.position = 'relative';
    }




    /**
     * Returns true if the file input has no files, false if it does.
     * @return {boolean}
     */
    isFileInputEmpty() {
        return this.inputElement.files.length === 0;
    }


    /**
     * Listen for the "change" event on the input
     */
    addHandleDroppedMediaListener() {
       this.inputElement.addEventListener('change', () => {
           if(this.triggerCustomEvents) {
               document.body.dispatchEvent(this.droppedEvent);
           }
           this.handleMedia();
       })
    }

    /**
     * Callback when the file input triggers the change event
     * Gets all the files in the input and instantiates the Preview class and calls the handlePreview method
     */
    handleMedia() {
        if(!this.inputElement.multiple) {
            this.removePreviousPreview();
        } else {
            this.removePreviousPreviews();
        }
        if(this.inputElement.files) {
            for(let i = 0; i < this.inputElement.files.length; i++) {
                let reader = new FileReader();
                reader.onload = (e) => {
                    let preview = new Preview({
                        file: this.inputElement.files[i],
                        imgSrc: e.target.result.toString(),
                        previewContainerElement: this.previewContainerElement,
                        previewImageContainerClass: this.previewImageContainerClass,
                        imageAltAttributeValue: this.imageAltAttributeValue,
                        removeImageIcon: this.removeImageIcon,
                        inputElement: this.inputElement
                    });
                    preview.handlePreview();
                    this.previews.push(preview);
                }
                reader.readAsDataURL(this.inputElement.files[i]);
            }
        }
    }

    removePreviousPreviews() {
        this.previews.forEach((preview) => {
           preview.removeImage();
        });
        this.previews = [];
    }

    removePreviousPreview() {
        let previews =  document.querySelectorAll(`#${this.previewContainerId} .${this.previewImageContainerClass}`);
        if(previews.length > 0) {
            previews.forEach((preview) => {
               preview.remove();
            });
        }
    }

    /**
     * Sets listeners and the styles that need to be applied for dragging, stop dragging and dropping.
     *
     * Sets classes on the target element if they are set. windowOnDragTargetClass is the class that is applied to the
     * target element while an element is being dragged in the window. onTargetDragOverClass is the class that is
     * applied to the target element when the dragged element is directly over the target element.
     *
     * Body fires custom events for custom handling, events are 'dragging', 'draggingStopped', 'dropped'.
     * @see setCustomEvents
     */
    addStylesOnDragDrop() {
        document.body.addEventListener('dragover', (e) => {
            if(this.windowOnDragTargetClass) {    // Add class on dragover if it's set
                this.targetElement.classList.add(this.windowOnDragTargetClass)
            }
            if(!this.isDragging) {
                if(this.triggerCustomEvents) {
                    document.body.dispatchEvent(this.draggingEvent);
                }
                this.isDragging = true;
            }
        })
        document.body.addEventListener('dragleave', (e) => {
            if(e.fromElement === null) { // Check if the dragged element actually left the window
                this.isDragging = false;
                if(this.windowOnDragTargetClass) {
                    this.targetElement.classList.remove(this.windowOnDragTargetClass)
                }
                if(this.triggerCustomEvents) {
                    document.body.dispatchEvent(this.draggingStoppedEvent);
                }
            }
        })
        document.body.addEventListener('drop', (e) => {
            this.isDragging = false;
            if(this.windowOnDragTargetClass) {
                this.targetElement.classList.remove(this.windowOnDragTargetClass)
            }
            if(this.onTargetDragOverClass) {
                this.targetElement.classList.remove(this.onTargetDragOverClass)
            }
            if(this.triggerCustomEvents) {
                document.body.dispatchEvent(this.draggingStoppedEvent);
            }
        })

        this.targetElement.addEventListener('dragover', () => {
            if(this.onTargetDragOverClass) {
                this.targetElement.classList.add(this.onTargetDragOverClass);
            }
        });

        this.targetElement.addEventListener('dragleave', (e) => {
                if(this.onTargetDragOverClass) {
                    this.targetElement.classList.remove(this.onTargetDragOverClass)
                }
        })
    }
}