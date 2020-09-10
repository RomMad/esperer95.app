import DisplayFields from '../utils/displayFields'

export default class UpdateDevice {

    constructor() {
        this.accommodationSelect = document.getElementById('device_accommodation')
        this.contributionSelect = document.getElementById('device_contribution')
        this.contributionTypeSelect = document.getElementById('device_contribution_type')
        this.contributionRateSelect = document.getElementById('device_contribution_rate')
        this.prefix = 'device_'
        this.init()
    }

    init() {
        new DisplayFields(this.prefix, 'accommodation', [1])
        new DisplayFields(this.prefix, 'contribution', [1])
        new DisplayFields(this.prefix, 'contributionType', [1, 3])
    }
}