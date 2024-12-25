export default class InputAjaxSearch {
    entities = [];
    indexCounter = 0;
    constructor(props) {
        this.containerId = props.containerId;
        this.container = document.getElementById(this.containerId);
        this.searchInputId = props.searchInputId;
        this.searchInput = document.getElementById(this.searchInputId);
        this.entityOptionsContainer = document.getElementById('entityOptions');
        this.isLoading = false;
        this.offsetSearch = 0;
        this.entitiesPerFetch = 10;
        this.limit = 10;
        this.optionClickCallback = props.optionClickCallback;
        this.fetchUrl = props.fetchUrl;
        this.allowFetching = true;
        this.packageName = props.packageName ?? '';
        this.searchAllOnIconClick = props.searchAllOnIconClick ?? false;
        this.searchIcon = this.container.querySelector('svg');
    }

    init() {
        this.addInputListener();
        this.closeOptionsOnDocumentClickListener();
        this.addLazyLoadOnScrollListener();
        this.addStyles();
    }

    addStyles() {
        this.searchIcon.style.cursor = 'pointer';
    }

    /**
     *  Append new suppliers when the user passes a certain threshold when scrolling
     */
    addLazyLoadOnScrollListener()
    {
        if(this.entityOptionsContainer) {
            this.entityOptionsContainer.addEventListener('scroll', () => {
                if(this.allowFetching === true) {
                    let offset = this.entityOptionsContainer.offsetHeight + this.entityOptionsContainer.scrollTop;
                    let chunk = this.entityOptionsContainer.scrollHeight;
                    if (this.isLoading === false) {
                        if (offset >= chunk - 40) {
                            this.offsetSearch += this.entitiesPerFetch;
                            this.appendEntities(this.searchInput.value, this.offsetSearch);
                        }
                    }
                }
            })
        }
    }

    /**
     * Removes the option container if the user clicks elsewhere in the document
     */
    closeOptionsOnDocumentClickListener()
    {
        document.addEventListener('mousedown',(event) => {
            if(event.target !== this.container && !this.container.contains(event.target)) {
                this.hideOptionsContainer();
            }
        });
    }

    addInputListener() {
        let timeout;
        this.searchInput.addEventListener('input', () => {
            clearTimeout(timeout);
            if (this.searchInput.value.length < 3 && this.searchInput.value !== '' || this.searchInput.value === '') {
                this.hideOptionsContainer();
                return;
            }
            timeout = setTimeout(() =>{
                this.showOptionsContainer();
                this.allowFetching = true;
                this.offsetSearch = 0;
                this.entityOptionsContainer.innerHTML = '';
                let searchInputValue = this.searchInput.value;
                this.appendEntities(searchInputValue,this.offsetSearch, this.limit);
            },1000)
        });
        if(this.searchAllOnIconClick) {
            this.searchIcon.addEventListener('click', () => {
                this.searchInput.value = '';
                this.showOptionsContainer();
                this.allowFetching = true;
                this.offsetSearch = 0;
                this.entityOptionsContainer.innerHTML = '';
                this.appendEntities(null, this.offsetSearch, this.limit);
            });
        }
    }

    showOptionsContainer() {
        this.entityOptionsContainer.classList.remove('hidden');
    }

    hideOptionsContainer() {
        this.entityOptionsContainer.classList.add('hidden');
        this.offsetSearch = 0;
    }

    /**
     * Appends entity options based on the fetched entities to the entityOptions element,
     * and calls the method to add their listeners
     * @param searchInput The string that the user typed in
     * @param offset The offset - number of times we already fetched
     */
    appendEntities(searchInput, offset)
    {
        let loader = document.createElement('div');
        loader.id = 'loaderAjax';

        this.container.appendChild(loader);
        this.fetchEntities(searchInput, offset).then((response) => {
            if(document.getElementById('loaderAjax')) {
                document.getElementById('loaderAjax').remove();
            }
            let entities = response;
            if(entities.length > 0) {
                entities.forEach((entity) => {
                    this.entities.push(entity)
                });
                entities.forEach((entity) => {
                    let entityOption = document.createElement('div');
                    entityOption.classList.add('entityOption');
                    entityOption.setAttribute('data-index', this.indexCounter.toString());
                    entityOption.addEventListener('click',() => {
                        this.optionClickCallback(this.entities[entityOption.getAttribute('data-index')]);
                        this.hideOptionsContainer();
                        this.searchInput.value = '';
                    })
                    // If the entity has no name property, try to access packageName+Name
                    if(this.packageName !== '') {
                        entityOption.innerText = entity[`${this.packageName}Name`];
                    } else {
                        entityOption.innerText = entity.name;
                    }
                    this.entityOptionsContainer.appendChild(entityOption);
                    this.indexCounter++;
                })
            } else {
                this.allowFetching = false;
                if(!document.getElementById('noMoreMessage')) {
                    let messageElem = document.createElement('span');
                    messageElem.id = 'noMoreMessage';
                    messageElem.innerText = response.message;
                    this.entityOptionsContainer.appendChild(messageElem);
                    this.entityOptionsContainer.scrollTop = this.entityOptionsContainer.scrollHeight;
                }
            }

        }).then(() => {
            this.isLoading = false;
        })
    }

    /**
     * Fetch the entities and return a promise
     * @returns {Promise<*>}
     */
    async fetchEntities(searchInput, offset)
    {
        this.isLoading = true;
        let data = await fetch(`${this.fetchUrl}${searchInput}&offset=${offset}`);
        let entities = await data.text();
        console.log(entities);
        return await JSON.parse(entities);
    }
}