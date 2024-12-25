import TextEditorHandler from "../Blocks/TextEditor/TextEditorHandler.js";
import ImageHandler from "./Image/ImageHandler.js";
import GalleryHandler from "./Gallery/GalleryHandler.js";
import EmbedHandler from "./Embed/EmbedHandler.js";
import SliderHandler from "./Slider/SliderHandler.js";
import EntityListHandler from "./EntityList/EntityListHandler.js";
import BannerHandler from "./Banner/BannerHandler.js";
import PersonListHandler from "./PersonList/PersonListHandler.js";
import QuoteHandler from "./Quote/QuoteHandler.js";
import ContentListHandler from "./ContentList/ContentListHandler.js";
import HeadingHandler from "./Heading/HeadingHandler.js";

export default class BlockHandlerCollection {

    constructor(blockConfig = {}) {
        this.blockConfig = blockConfig;
        this.setBlockHandlers();
    }

    setBlockHandlers() {
        this.blockHandlers = {
            textEditorHandler: new TextEditorHandler(),
            imageHandler: new ImageHandler(),
            galleryHandler: new GalleryHandler(),
            embedHandler: new EmbedHandler(),
            sliderHandler: new SliderHandler(),
            entityListHandler: new EntityListHandler(this.blockConfig),
            bannerHandler: new BannerHandler(),
            personListHandler: new PersonListHandler(),
            quoteHandler: new QuoteHandler(),
            contentListHandler: new ContentListHandler(),
            headingHandler: new HeadingHandler()
        }
    }

    getBlockHandlers() {
        return this.blockHandlers;
    }

    getBlockHandler(handlerName) {
        return this.blockHandlers[handlerName];
    }

    addBlockHandler(key, handler) {
        if(!this.blockHandlers[key]) {
            this.blockHandlers[key] = handler;
            return true;
        }
        throw new Error('Block handler with the provided key already exists.');
    }

    removeBlockHandler(key) {
        if(this.getBlockHandler(key)) {
            delete this.getBlockHandlers()[key];
        }
    }

    getBlockHandlerKeys() {
        const keys = [];
        Object.keys(this.getBlockHandlers()).forEach((key) => {
           keys.push(key);
        });
        return keys;
    }

}