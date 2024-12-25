import BlockHandler from "../BlockHandler.js";
import SliderBlock from "./SliderBlock.js";
import {sliderAssets} from "./sliderAssets.js";

export default class SliderHandler extends BlockHandler {
    blockName = 'Slider';
    constructor() {
        super();
    }

    getBlockSvg() {
        return sliderAssets.blockIcon;
    }

    getBlock(id, data = null) {
        return new SliderBlock(id, this.blockName, this.eventEmitter, data);
    }
}