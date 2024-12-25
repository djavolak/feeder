import PageFactory from "./gfComponents/pages/PageFactory.js";
import {dataTableSelectors} from "https://skeletor.greenfriends.systems/dtables/1.x/0.1/src/DataTable/dataTableSelectors.js";
import EventEmitter from "https://skeletor.greenfriends.systems/dtables/1.x/master/src/EventEmitter/EventEmitter.js";
import MediaLibrary from "./gfComponents/mediaLibrary/MediaLibrary.js";

$(document).ready(function() {
    let userId = $('#userId').attr('data-userId');

    var urlParams = new URLSearchParams(window.location.search);

    $('.datepicker').datepicker({
        dateFormat: 'yy-m-d'
    });

    //categories dropdown
    $('.topLevel').sortable({
        handle: "h2",
        axis: 'y',
        cursor: 'move',
        items: '.accordionFirstLevel',
    });
    $('.secondLevel').sortable({
        handle: "h4",
        axis: 'y',
        cursor: 'move',
        items: '.accordionSecondLevel',
    });
    $('.thirdLevel').sortable({
        handle: "h5",
        axis: 'y',
        cursor: 'move',
        items: '.thirdLevelItem',
    });
    $('.accordionFirstLevel').accordion({
        collapsible: true,
        header: ">h2",
        heightStyle: "content",
        active:false,
        icons: { "header": "ui-icon-plus", "activeHeader": "ui-icon-minus" }
    });
    $('.accordionSecondLevel').accordion({
        collapsible: true,
        header: ">h4",
        heightStyle: "content",
        active:false,
        icons: { "header": "ui-icon-plus", "activeHeader": "ui-icon-minus" }
    });
});
window.mediaLibrary = new MediaLibrary(new EventEmitter());
mediaLibrary.mount();
if (document.getElementById(dataTableSelectors.ids.table)) {
    document.addEventListener('DOMContentLoaded', () => {
        const pageIdentifier = document.getElementById(dataTableSelectors.ids.table).getAttribute(dataTableSelectors.attributes.page);
        console.log(pageIdentifier);
        const pageEntity = PageFactory.make(pageIdentifier);
        console.log(pageEntity);
        pageEntity.init();
    });
}