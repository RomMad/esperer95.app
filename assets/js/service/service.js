import DeleteTr from '../utils/deleteTr'
import CheckChange from '../utils/checkChange'
import AddCollectionWidget from '../utils/addCollectionWidget'
import UpdateService from './updateService'
import SearchLocation from '../utils/searchLocation'
import '../utils/maskPhone'

document.addEventListener('DOMContentLoaded', () => {
    new DeleteTr('function-table')
    new CheckChange('service') // form name
    new AddCollectionWidget()
    new UpdateService()
    new SearchLocation('service_location')
})