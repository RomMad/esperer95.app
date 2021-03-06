import Ajax from '../utils/ajax'
import Loader from './loader'

/**
 * Gère la recherche d'une adresse avec l'API adresse.data.gouv.fr
 */
export default class SearchLocation {

    constructor(containerId, locationType = 'address', codeDepartement = '95', lengthSearch = 3, time = 500, limit = 5, lat = 49.04, lon = 2.04) {
        this.containerElt = document.getElementById(containerId)
        if (this.containerElt) {
            this.loader = new Loader()
            this.ajax = new Ajax(this.loader)
            this.searchElt = this.containerElt.querySelector('.js-search')
            this.addressElt = this.containerElt.querySelector('.js-address')
            this.cityElt = this.containerElt.querySelector('.js-city')
            this.zipcodeElt = this.containerElt.querySelector('.js-zipcode')
            this.locationIdElt = this.containerElt.querySelector('.js-locationId')
            this.latElt = this.containerElt.querySelector('.js-lat')
            this.lonElt = this.containerElt.querySelector('.js-lon')
            this.resultsSearchElt = this.createResultsListElt()
            this.locationType = locationType
            this.codeDepartement = codeDepartement
            this.lengthSearch = lengthSearch // Nombre de caractères minimum pour lancer la recherche
            this.time = time // Durée en millisecondes
            this.limit = limit // Nombre d'éléments retournés
            this.lat = lat // Latitude
            this.lon = lon // Longitude
            this.results = null
            this.countdownID = null
            this.init()
        }
    }

    init() {
        this.searchElt.addEventListener('keyup', this.timer.bind(this))
    }

    /**
     * Crée la liste des résultats.
     * @return {HTMLDivElement}
     */
    createResultsListElt() {
        const resultsListElt = document.createElement('div')
        resultsListElt.id = 'results_list_location'
        resultsListElt.className = 'w-100 list-group d-block fade-in position-absolute z-index-999'
        if (this.cityElt) {
            this.searchElt.parentNode.appendChild(resultsListElt)
        } else {
            this.searchElt.parentNode.parentNode.appendChild(resultsListElt)
        }

        return resultsListElt
    }

    /**
     * Timer avant de lancer la requête Ajax.
     */
    timer() {
        clearInterval(this.countdownID)
        this.countdownID = setTimeout(this.count.bind(this), this.time)
    }

    /**
     * Compte le nombre de caractères saisis et lance la requête Ajax.
     */
    count() {
        let valueSearch = this.searchElt.value
        if (valueSearch.length >= this.lengthSearch) {
            this.loader.on()
            let valueSearch = this.searchElt.value.replace(' ', '+')
            const geo = `&lat=${this.lat}&lon=${this.lon}` // Donne une priorité géographique

            let url = 'https://api-adresse.data.gouv.fr/search/?q=' + valueSearch + geo + '&limit=' + this.limit

            if (this.locationType === 'city') {
                url = 'https://geo.api.gouv.fr/communes?nom=' + valueSearch + '&codeDepartement=' + this.codeDepartement + '&limit=' + this.limit
            }

            this.ajax.send('GET', url, this.responseAjax.bind(this))
        }
    }

    /**
     * Donne la réponse Ajax.
     * @param {Object} data 
     */
    responseAjax(data) {

        if (this.locationType === 'address') {
            this.results = data.features
        }
        if (this.locationType === 'city') {
            this.results = data
        }

        this.resultsSearchElt.innerHTML = ''
        if (this.results.length > 0) {
            this.addItem()
        } else {
            this.displayNoResult()
        }
        this.resultsSearchElt.classList.replace('d-none', 'd-block')
        this.resultsSearchElt.classList.replace('fade-out', 'fade-in')
        this.loader.off()

        document.addEventListener('click', e => {
            if (e.target.id != 'support_location_search') {
                this.hideListResults()
            }
        })
    }

    /**
     * Ajoute un élément à la liste des résultats.
     */
    addItem() {
        let i = 0
        this.results.forEach(result => {
            let itemElt = this.createItem(result, i)
            this.resultsSearchElt.appendChild(itemElt)
            itemElt.addEventListener('click', () => {
                this.updateLocation(itemElt.getAttribute('data-result'))
            })
            i++
        })
        this.setWidthResultsSearchElt()
    }

    /**
     * Créé un élément de résultat.
     * @param {Array} result 
     * @param {Number} i 
     * @return {HTMLElement}
     */
    createItem(result, i) {
        const itemElt = document.createElement('a')
        itemElt.innerHTML = `<span class='text-secondary small'>${this.getLabel(result)}</span>`
        itemElt.className = 'list-group-item list-group-item-action pl-3 pr-1 py-1 cursor-pointer'
        itemElt.setAttribute('data-result', i)

        return itemElt
    }

    /**
     * Donne le label de la recherche.
     * @param {Array} result 
     */
    getLabel(result) {
        if (this.locationType === 'city') {
            return result.nom + ' (' + result.codesPostaux[0] + ')'
        }

        let label = result.properties.label
        if (result.properties.type === 'municipality') {
            label = label + ' (' + result.properties.context + ')'
        }
        return label
    }

    /**
     * Modifie la largeur de l'élément avec la liste des résultats.
     */
    setWidthResultsSearchElt() {
        const styleSeachElt = window.getComputedStyle(this.searchElt)
        this.resultsSearchElt.style.maxWidth = styleSeachElt.width
        this.resultsSearchElt.style.top = styleSeachElt.height
    }

    // Met à jour les champs du formulaire.
    updateLocation(i) {
        const result = this.results[i]

        if (this.locationType === 'city') {
            this.cityElt.value = result.nom
        }

        if (this.locationType === 'address') {
            this.searchElt.value = result.properties.label
            if (this.addressElt) {
                this.cityElt.value = result.properties.city
                this.addressElt.value = result.properties.name
                this.zipcodeElt.value = result.properties.postcode
            }
            if (this.locationIdElt) {
                this.locationIdElt.value = result.properties.id
                this.lonElt.value = result.geometry.coordinates[0]
                this.latElt.value = result.geometry.coordinates[1]
            }
        }
    }

    /**
     * Affiche 'Aucun résultat.'
     */
    displayNoResult() {
        const spanElt = document.createElement('p')
        spanElt.textContent = 'Aucun résultat.'
        spanElt.className = 'list-group-item pl-3 py-2 text-secondary small'
        this.resultsSearchElt.appendChild(spanElt)
    }

    /**
     * Supprime la liste des résultats au clic.
     */
    hideListResults() {
        this.resultsSearchElt.classList.replace('fade-in', 'fade-out')
        setTimeout(() => {
            this.resultsSearchElt.classList.replace('d-block', 'd-none')
        }, 300)
    }
}