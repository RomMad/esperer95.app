/**
 * Permet d'obtenir ou de définir l'option d'un élément <select>.
 */
export default class SelectType {

    /**
     * Donne la valeur de l'option sélectionnée.
     * @param {HTMLSelectElement} selectElt 
     * @return {Number}
     */
    getOption(selectElt) {
        if (selectElt === null) {
            return console.error('selectElt is null !')
        }
        
        let value = false
        selectElt.querySelectorAll('option').forEach(optionElt => {
            if (optionElt.value && (optionElt.selected || true === optionElt.selected)) {
                value = parseInt(optionElt.value)
            }
        })

        return value
    }

    /**
     * Définie l'option sélectionnée.
     * @param {HTMLSelectElement} selectElt 
     * @param {String} value 
     */
    setOption(selectElt, value) {
        if (selectElt === null) {
            return console.error('selectElt is null !')
        }

        selectElt.querySelectorAll('option').forEach(optionElt => {
            if (parseInt(optionElt.value) === parseInt(value)) {
                return optionElt.selected = true
            }
            return optionElt.selected = false
        })
    }
}