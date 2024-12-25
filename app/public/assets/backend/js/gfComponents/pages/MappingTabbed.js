import BaseTabbedPage from "https://skeletor.greenfriends.systems/gfcomponents/0.x.x/components/BaseTabbedPage.js";

document.addEventListener('DOMContentLoaded', () => {
    getMasterCategories().then((masterCategories) => {
        let tabbedPage = new BaseTabbedPage();
        tabbedPage.init();

        initSearchSource(tabbedPage);

        document.body.addEventListener('dataChanged', async (e) => {
            let activePagination = document.querySelector('.activePagination');
            if(activePagination) {
                await tabbedPage.doAction(activePagination, activePagination.getAttribute('data-action'), 'GET', e);
            }
        })

        // Remove class names from pagination because the flow demands it
        tabbedPage.tabs.forEach((tab) => {
           tab.addEventListener('click', () => {
               clearSearchSourceInput();
               removeOldEventClassesFromPagination();
           });
        });


        tabbedPage.tabbedContentContainer.addEventListener('mappedReady', () => {
            highlightCategoryIfIdInRoute();
            addSearchListeners(masterCategories);
            removeSearchListener();
            handlePagination('mappedReady', tabbedPage);
            let marginButtons = document.querySelectorAll('.marginControl');
            let deleteButtons = document.querySelectorAll('.delete');
            let selectCategoryButtons = document.querySelectorAll('.form-group select');
            marginButtons.forEach((marginButton) => {
                handleMarginControlForButton(marginButton, tabbedPage.helper, tabbedPage.fetcher);
            });
            deleteButtons.forEach((elem) => {
                handleDeleteButton(elem, tabbedPage.helper, tabbedPage.fetcher);
            });
            selectCategoryButtons.forEach((selectCategoryButton) => {
                handleSelectCategory(selectCategoryButton, tabbedPage.helper, tabbedPage.fetcher, 'mapped');
            });
        });



        tabbedPage.tabbedContentContainer.addEventListener('unmappedReady', () => {
            addSearchListeners(masterCategories);
            removeSearchListener();
            handlePagination( 'unmappedReady',tabbedPage);
            let marginButtons = document.querySelectorAll('.marginControl');
            let deleteButtons = document.querySelectorAll('.delete');
            let selectCategoryButtons = document.querySelectorAll('.form-group select');
            marginButtons.forEach((marginButton) => {
                handleMarginControlForButton(marginButton, tabbedPage.helper, tabbedPage.fetcher);
            });
            deleteButtons.forEach((elem) => {
                handleDeleteButton(elem, tabbedPage.helper, tabbedPage.fetcher);
            });
            selectCategoryButtons.forEach((selectCategoryButton) => {
                handleSelectCategory(selectCategoryButton, tabbedPage.helper, tabbedPage.fetcher, 'unmapped');
            });
        });



        tabbedPage.tabbedContentContainer.addEventListener('ignoredReady', () => {
            addSearchListeners(masterCategories);
            removeSearchListener();
            handlePagination( 'ignoredReady',tabbedPage);
            let marginButtons = document.querySelectorAll('.marginControl');
            let deleteButtons = document.querySelectorAll('.delete');
            let selectCategoryButtons = document.querySelectorAll('.form-group select');
            marginButtons.forEach((marginButton) => {
                handleMarginControlForButton(marginButton, tabbedPage.helper, tabbedPage.fetcher);
            });
            deleteButtons.forEach((elem) => {
                handleDeleteButton(elem, tabbedPage.helper, tabbedPage.fetcher);
            });
            selectCategoryButtons.forEach((selectCategoryButton) => {
                handleSelectCategory(selectCategoryButton, tabbedPage.helper, tabbedPage.fetcher, 'ignored');
            });
        });

        document.body.addEventListener('marginFormReady', (e) => {
            let formInModal = tabbedPage.helper.getFormInModal();
            let submitButton = formInModal.querySelector('#submitMarginRules');
            let fixedMarginInput = formInModal.querySelector('#fixedMargin')
            submitButton.addEventListener('click', async (e) => {
                await doAction(tabbedPage.helper, tabbedPage.fetcher, e, submitButton,
                    'small', submitButton.getAttribute('data-action'),
                    'POST','marginsUpdated',new FormData(formInModal));
            });

            let addRule = document.getElementById('addRule');
            handleFixedMarginStuff(fixedMarginInput, addRule);
            fixedMarginInput.addEventListener('input', () => {
                handleFixedMarginStuff(fixedMarginInput, addRule);
                let marginGroupIdInput = document.getElementById('marginGroupInputId');
                if(marginGroupIdInput) {
                    marginGroupIdInput.remove();
                }
                document.getElementById('marginGroup').value = '-1';
                document.querySelectorAll('.marginRuleFromGroup').forEach((ruleFromGroup) => {
                    ruleFromGroup.remove();
                });
            });
            let existingRules = document.querySelectorAll('.deleteMargin');
            existingRules.forEach((rule) => {
                addDeleteMarginListener(rule);
            });
            addRule.addEventListener('click', (e) => {
                e.preventDefault();
                let marginGroupIdInput = document.getElementById('marginGroupInputId');
                if(marginGroupIdInput) {
                    marginGroupIdInput.remove();
                }
                document.getElementById('marginGroup').value = '-1';
                document.querySelectorAll('.marginRuleFromGroup').forEach((ruleFromGroup) => {
                   ruleFromGroup.remove();
                });
                let marginRulesContainer = document.getElementById('marginRulesContainer');
                let marginTemplate = document.getElementById('marginRuleTemplate');
                let templateContent = marginTemplate.content.cloneNode(true);
                let deleteButtonInNewMarginRule = templateContent.querySelector('.deleteMargin');
                marginRulesContainer.appendChild(templateContent);
                addDeleteMarginListener(deleteButtonInNewMarginRule);
            });

            let addGroup = document.getElementById('addGroup');
            addGroup.addEventListener('click', async (e) => {
                e.preventDefault();
                let groupId = document.getElementById('marginGroup').value;
                if(groupId === '-1') {
                    return;
                }
                document.querySelectorAll('.marginRuleFormGroup').forEach((inputGroup) => {
                   inputGroup.remove();
                });
                let html = await getGroupHtml(groupId);
                generateMarginGroupIdInput(groupId);
                document.getElementById('marginRulesContainer').insertAdjacentHTML('beforeend',html);
                document.querySelectorAll('.deleteMarginNew').forEach((deleteMarginNew) => {
                   deleteMarginNew.addEventListener('click', () => {
                       deleteMarginNew.parentElement.remove();
                   });
                });
            })
        });


        function handleFixedMarginStuff(fixedMarginInput, addRuleButton) {
            let disabled =  false;
            if (fixedMarginInput.value !== '') {
                disabled =  true;
            }
            document.querySelectorAll('#marginRulesContainer .form-group:not(.fixedMarginInputs) input').forEach((elem) => {
                if (disabled) {
                    elem.setAttribute('readonly', 'true');
                    return;
                }
                elem.removeAttribute('readonly');
            })
            addRuleButton.disabled = disabled;
            document.getElementById('addGroup').disabled = disabled;
        }
        document.body.addEventListener('marginsUpdated', () => {
            tabbedPage.helper.closeModal();
        });

        let supplierSelect = document.querySelector('.supplierSelect');
        let applyFilter = document.getElementById('applyFilter');
        if(supplierSelect && applyFilter) {
            applyFilter.addEventListener('click', (e) => {
                e.stopImmediatePropagation();
                let basePath = supplierSelect.getAttribute('data-location');
                window.location.href = `${basePath}${supplierSelect.value}/`;
            });
        }
    });
});

async function getMasterCategories() {
    let action = document.getElementById('tabbedContentContainer').getAttribute('data-master-categories-action');
    let req = await fetch(action, {
        method: 'POST',
    });
    let masterCategories =  JSON.parse(await req.text());
    return [{value:'--- Ignoriši ---', id:-1},...masterCategories]
}

async function getGroupHtml(groupId) {
    let action = '/margin-groups/getGroupHtmlRules/';
    let data = new FormData();
    data.append('groupId', groupId);
    let req = await fetch(action, {
        method: 'POST',
        body: data
    });
    return JSON.parse(await req.text());
}


function handleMarginControlForButton(button, helper, fetcher) {
    button.addEventListener('click', async (e) => {
        e.preventDefault();
        let entityId = button.getAttribute('data-id') ?? null;
        if(entityId) {
            await doAction(helper, fetcher, e, button,
                'small', button.getAttribute('data-action'), 'GET','marginFormReady');
        }
    });
}

function handleDeleteButton(button, helper, fetcher) {
    button.addEventListener('click', async (e) => {
        e.preventDefault();
        let entityId = button.getAttribute('data-id') ?? null;
        if(entityId) {
            await doAction(helper, fetcher, e, button,
                'small', button.getAttribute('data-action'), 'POST', 'dataChanged');
        }
    });
}

function handleSelectCategory(select, helper, fetcher, tabName) {
    select.addEventListener('change', async (e) => {
        let mappedTab = document.querySelector('.mappedTab');
        let unmappedTab = document.querySelector('.unmappedTab');
        let ignoredTab = document.querySelector('.ignoredTab');
        e.preventDefault();
        let action = document.querySelector('form').getAttribute('action');
        let formData = new FormData();
        let parent = select.parentElement.parentElement.parentElement;
        formData.append('source1', parent.querySelector('input[name="source1[]"]').value);
        formData.append('source2', parent.querySelector('input[name="source2[]"]').value);
        formData.append('source3', parent.querySelector('input[name="source3[]"]').value);
        formData.append('categoryId', select.value);
        switch(tabName) {
            case 'mapped':
                formData.append('id', parent.querySelector('input[name="id[]"]').value);
                break;
            case 'unmapped':
            case 'ignored':
                formData.append('id', parent.querySelector('input[name="id[]"]').value);
                break;
        }

        let mappedDataCount = parseInt(mappedTab.getAttribute('data-count'));
        let ignoredDataCount = parseInt(ignoredTab.getAttribute('data-count'));
        let unmappedDataCount = parseInt(unmappedTab.getAttribute('data-count'));
        if(tabName === 'mapped') {
            if(select.value === '0') {
                unmappedTab.setAttribute('data-count', unmappedDataCount + 1);
                mappedTab.setAttribute('data-count', mappedDataCount - 1);
            }
            if(select.value === '-1') {
                ignoredTab.setAttribute('data-count', ignoredDataCount + 1);
                mappedTab.setAttribute('data-count', mappedDataCount - 1);
            }

            if((mappedDataCount-1) % 25 === 0) {
                handlePaginationOnChange();
            }
        }
        if(tabName === 'unmapped') {
            if(select.value !== '-1') {
                mappedTab.setAttribute('data-count', mappedDataCount + 1);
            }
            if(select.value === '-1') {
                ignoredTab.setAttribute('data-count', ignoredDataCount + 1);
            }
            unmappedTab.setAttribute('data-count', unmappedDataCount - 1);
            if((unmappedDataCount-1) % 25 === 0) {
                handlePaginationOnChange();
            }
        }
        if(tabName === 'ignored') {
            if(select.value === '0') {
                unmappedTab.setAttribute('data-count', unmappedDataCount + 1);
            }
            if(select.value !== '0') {
                mappedTab.setAttribute('data-count', mappedDataCount + 1);
            }
            ignoredTab.setAttribute('data-count', ignoredDataCount - 1);
            if((ignoredDataCount-1) % 25 === 0) {
                handlePaginationOnChange();
            }
        }
        await doAction(helper, fetcher, e, select,
            'small', action, 'POST', 'dataChanged',formData);

    });
}

function handlePaginationOnChange() {
    let paginationButtons = document.querySelectorAll('#pagination button');
    let lastButton = paginationButtons[paginationButtons.length - 1];
    if(lastButton.innerText !== '1') {
        if(lastButton === document.querySelector('.activePagination')) {
            let newPaginationButtons = document.querySelectorAll('#pagination button');
            newPaginationButtons[newPaginationButtons.length - 2].classList.add('activePagination');
        }
        lastButton.remove();
    }
}

async function doAction(helper, fetcher, e, initiator, loaderSize, action, method, eventName , formData = null,
                        modalWidth = '600px', modalHeight = '600px')
{
    e.preventDefault();
    if(isGetAndModalContentContainerExists(method, helper)) {
        helper.openModal(modalWidth, modalHeight);
        helper.startLoader(helper.getInnerModal(), loaderSize);
    } else {
        helper.removeErrorMessages();
        helper.startLoaderInElementParent(initiator, loaderSize);
    }
    let data = await fetcher.fetchData(action, method, formData);
    helper.stopLoader();
    if(isGetAndModalContentContainerExists(method, helper)) {
        helper.populateModal(data);
    } else {
        helper.makeElementVisibleAfterLoader(initiator);
    }
    try {
        let parsedData = JSON.parse(data);
        if(parsedData.message && parsedData.message !== '') {
            if (parsedData.message !== 'success' && parsedData.message !== 'Success') {
                alert(parsedData.message);
            }
        }
    } catch (e) {

    }
    document.body.dispatchEvent(new CustomEvent(eventName, {
        detail: {
            data: data
        }
    }))
}

function isGetAndModalContentContainerExists(method, helper) {
    return helper.getInnerModal() && method === 'GET';
}

function addDeleteMarginListener(elem) {
    elem.addEventListener('click', () => {
       elem.parentElement.remove();
    });
}

function highlightCategoryIfIdInRoute() {
    let path = window.location.pathname;
    let splitPath = path.split('/');
    splitPath = splitPath.filter((val) => {
       return val !== '';
    });
    let categoryId = splitPath[splitPath.length - 1];
    let selects = document.querySelectorAll('select');
    selects.forEach((select) => {
       if(select.value === categoryId && checkSourceValue(select)) {
           window.scroll({
               top: select.getBoundingClientRect().top + window.scrollY - 50,
               behavior: 'smooth'
           })
           select.style.border = '2px solid #4e73df';
           select.parentElement.parentElement.parentElement.querySelectorAll('input').forEach((input) => {
              input.style.border = '2px solid #4e73df';
           });
       }
    });
}

function checkSourceValue(select) {
    let parent = select.parentElement.parentElement.parentElement;
    let urlParams = new URLSearchParams(window.location.search);
    let sourceName = urlParams.get('supplierCategory');
    let match = false;
    if(sourceName !== null) {
        let names = parent.getAttribute('data-names');
        if(sourceName === names) {
            match = true;
        }
    }
    return match;
}

function addSearchListeners(masterCategories) {
    let searchButton = document.querySelectorAll('.searchCategory');
    searchButton.forEach((button) => {
        button.addEventListener('click', () => {
            if(document.getElementById('categorySearchInput')) {
                return;
            }
            let parent = button.parentElement;
            let selectElement = parent.parentElement.querySelector('select');
            let search = generateSearch();
            parent.appendChild(search);
            search.querySelector('input').focus();
            search.querySelector('.scrollableContainerCustomHeight').appendChild(generateResults(masterCategories, selectElement));
        });
    });
}

function generateSearch() {
    let container = document.createElement('div');
    container.classList.add('categorySearchInputContainer')
    let searchInput = document.createElement('input');
    searchInput.classList.add('form-control');
    searchInput.id = 'categorySearchInput';

    let resultsContainer = document.createElement('div');
    resultsContainer.classList.add('scrollableContainerCustomHeight');

    let timeout = null;
    searchInput.addEventListener('input', () => {
        filterResults(searchInput.value, resultsContainer.querySelectorAll('p'));
        clearTimeout(timeout);
        timeout = setTimeout(() => {
        },100)

    });
    container.appendChild(searchInput);
    container.appendChild(resultsContainer);
    return container;
}

function removeSearch() {
    document.getElementById('categorySearchInput').parentElement.remove();
}

function generateResults(results, selectElement) {
    let wrapper = document.createElement('div');
    results.forEach((result) => {
        let resultElem = document.createElement('p');
        resultElem.innerText = result.value;
        resultElem.setAttribute('id', result.id);
        resultElem.addEventListener('click', () => {
            selectElement.value = result.id.toString();
            selectElement.dispatchEvent(new Event('change'));
            removeSearch();
        });
        wrapper.appendChild(resultElem);
    });
    return wrapper;
}

function filterResults(value, targets) {
    targets.forEach((target) => {
        if(transliterate(target.innerText).toLowerCase().includes(value.toLowerCase())) {
            target.style.display = 'block';
        } else {
            target.style.display = 'none';
        }
    });
}

function transliterate(string) {
    return string
        .replace(/ž/g, 'z')
        .replace(/š/g, 's')
        .replace(/đ/g, 'dj')
        .replace(/č/g, 'c')
        .replace(/ć/g, 'c')
        .replace(/Ž/g, 'z')
        .replace(/Š/g, 's')
        .replace(/Đ/g, 'dj')
        .replace(/Č/g, 'c')
        .replace(/Ć/g, 'c');
}

function removeSearchListener() {
    document.body.addEventListener('mousedown', (e) => {
        let inputSearchContainer = document.getElementById('categorySearchInput')?.parentElement.parentElement;
        if(inputSearchContainer && !inputSearchContainer.contains(e.target)) {
            removeSearch();
        }
    });
}


function handlePagination(eventName, tabbedPage, force = false) {
    if(document.querySelector(`#pagination.${eventName}`) && !force) {
        return;
    }
    let activeTab = document.querySelector('.tab.active');
    let count = activeTab.getAttribute('data-count');
    generatePagination(count, activeTab, eventName, tabbedPage);
}

function generatePagination(count, activeTab, eventName, tabbedPage) {
    let numOfPages = Math.ceil(count / 25);
    let paginationContainer = document.getElementById('pagination');
    if(paginationContainer) {
        paginationContainer.classList.add(eventName);
        paginationContainer.innerHTML = '';
        for (let i = 1; i <= numOfPages; i++) {
            paginationContainer.appendChild(generatePaginationButton(i, numOfPages, activeTab, eventName, tabbedPage));
        }
    }
}

function generatePaginationButton(index, numOfPages, activeTab, eventName, tabbedPage) {
    let paginationButton = document.createElement('button');
    paginationButton.classList.add('btn','btn-primary');
    paginationButton.innerText = index.toString();
    let baseActionWithParams = activeTab.getAttribute('data-action');
    let baseAction =  baseActionWithParams.substring(0, baseActionWithParams.indexOf('?'));
    paginationButton.setAttribute('data-action',
        (baseAction !== '' ? baseAction : baseActionWithParams) + `?page=${index}&limit=25`
    );
    paginationButton.setAttribute('data-form-ready-event-name', eventName);
    let pageFromGet = getPageFromGet();
    if (
        (pageFromGet && parseInt(pageFromGet) === index) ||
        (!pageFromGet && index === 1) ||
        (pageFromGet && index === 1 && pageFromGet > numOfPages)
        ) {
        addActivePaginationClass(paginationButton);
    }
    addPaginationButtonListener(paginationButton, tabbedPage);
    return paginationButton;
}

function addPaginationButtonListener(paginationButton, tabbedPage) {
    paginationButton.addEventListener('click', async (e) => {
        if(paginationButton.classList.contains('activePagination')) {
            return;
        }
        removeGetParams();
        removeActivePaginationClass();
        addActivePaginationClass(paginationButton);
        await tabbedPage.doAction(paginationButton, paginationButton.getAttribute('data-action'), 'GET', e);
    });
}

function removeActivePaginationClass() {
    document.querySelector('.activePagination').classList.remove('activePagination');
}

function addActivePaginationClass(button) {
    button.classList.add('activePagination');
}

function removeOldEventClassesFromPagination() {
    let pagination = document.getElementById('pagination');
    if(pagination) {
        pagination.classList.remove('mappedReady', 'unmappedReady', 'ignoredReady');
    }
}

function getPageFromGet() {
    let url = new URLSearchParams(window.location.search);
    if(url.get('page') && parseInt(url.get('page')) <= 0) {
        return null;
    }
    return url.get('page');
}

function removeGetParams() {
    window.history.replaceState({}, null, window.location.pathname);
}

function generateMarginGroupIdInput(groupId) {
    let marginGroupInput = document.getElementById('marginGroupInputId');
    if(marginGroupInput) {
        marginGroupInput.remove();
    }
    let input = document.createElement('input');
    input.id = 'marginGroupInputId';
    input.value = groupId;
    input.type = 'hidden';
    input.name = 'marginGroupInputId';
    document.getElementById('marginRulesContainer').appendChild(input);
}

function initSearchSource(tabbedPage) {
    let searchSourceInput = document.getElementById('searchSource');
    let timeout = null;
    searchSourceInput.addEventListener('input', async (e) => {
        let type = getActiveType();
        if(!type) {
            return;
        }
        if(searchSourceInput.value === '') {
            let target = document.querySelector(`.${type}Tab`);
            await tabbedPage.doAction(target, target.getAttribute('data-action'), 'GET', e);
            handlePagination(`${type}Ready`, tabbedPage, true);
            return;
        }
        clearTimeout(timeout);
        timeout = setTimeout(async () => {
            searchSourceInput.setAttribute('data-form-ready-event-name', `${type}Ready`);
            let supplierId = document.querySelector('.supplierSelect').value;
            let searchType = document.querySelector('input[name="searchType"]:checked').value;
            let action = `/category-mapper/fetchResultsWithSearchSource/?search=${searchSourceInput.value}&type=${type}&supplierId=${supplierId}&mappingType=internal&searchType=${searchType}`;
            await tabbedPage.doAction(searchSourceInput, action, 'GET', e);
            let countResults = document.getElementById('searchCount').getAttribute('data-count');
            generateSearchPagination(countResults, `${type}Ready`, action, tabbedPage);
        }, 500)
    });
    document.querySelectorAll('input[name="searchType"]').forEach((radio) => {
       radio.addEventListener('click', async (e) => {
           let type = getActiveType();
           if(!type) {
               return;
           }
           if(searchSourceInput.value === '') {
               return;
           }
           clearTimeout(timeout);
           timeout = setTimeout(async () => {
               searchSourceInput.setAttribute('data-form-ready-event-name', `${type}Ready`);
               let supplierId = document.querySelector('.supplierSelect').value;
               let searchType = document.querySelector('input[name="searchType"]:checked').value;
               let action = `/category-mapper/fetchResultsWithSearchSource/?search=${searchSourceInput.value}&type=${type}&supplierId=${supplierId}&mappingType=internal&searchType=${searchType}`;
               await tabbedPage.doAction(searchSourceInput, action, 'GET', e);
               let countResults = document.getElementById('searchCount').getAttribute('data-count');
               generateSearchPagination(countResults, `${type}Ready`, action, tabbedPage);
           }, 500)
       });
    });

}

function generateSearchPagination(count, eventName, action, tabbedPage) {
    let numOfPages = Math.ceil(count / 25);
    let paginationContainer = document.getElementById('pagination');
    if(paginationContainer) {
        paginationContainer.classList.add(eventName);
        paginationContainer.innerHTML = '';
        for (let i = 1; i <= numOfPages; i++) {
            paginationContainer.appendChild(generatePaginationSearchButton(i, numOfPages, action, eventName, tabbedPage));
        }
    }
}

function generatePaginationSearchButton(index, numOfPages, action, eventName, tabbedPage) {
    let paginationButton = document.createElement('button');
    paginationButton.classList.add('btn','btn-primary');
    paginationButton.innerText = index.toString();
    paginationButton.setAttribute('data-action',
        action + `&page=${index}&limit=25`
    );
    paginationButton.setAttribute('data-form-ready-event-name', eventName);
    let pageFromGet = getPageFromGet();
    if (
        (pageFromGet && parseInt(pageFromGet) === index) ||
        (!pageFromGet && index === 1) ||
        (pageFromGet && index === 1 && pageFromGet > numOfPages)
    ) {
        addActivePaginationClass(paginationButton);
    }
    addPaginationButtonListener(paginationButton, tabbedPage);
    return paginationButton;
}

function getActiveType() {
    let activeTab = document.querySelector('.active.tab');
    if(activeTab) {
        return activeTab.getAttribute('data-type');
    }
    return null;
}

function clearSearchSourceInput() {
    document.getElementById('searchSource').value = '';
}