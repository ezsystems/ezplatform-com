class GoogleAnalyticsService {

    constructor () {
        this.gaDataAttr = 'data-ga-action';
        this.gaCategoryAttr = 'data-ga-category';
        this.gaLabelAttr = 'data-ga-label';
    }

    rewriteByChildElement (element) {
        if (element.classList.contains('ga-link-listener')) {
            const childElement = element.querySelector('a');

            return this.rewriteDataAttributes(element, childElement);
        }

        return element;
    }

    getGAItemObject (element) {
        return {
            hitType: 'event',
            eventCategory: this.getGACategory(),
            eventAction: this.getGAAction(element),
            eventLabel: this.getGALabel(element)
        };
    }

    getGAAction (element) {
        return element.getAttribute(this.gaDataAttr);
    }

    getGACategory () {
        return window.eZ.helpers.stringUtils.capitalize(document.body.getAttribute(this.gaCategoryAttr));
    }

    getGALabel (element) {
        return element.getAttribute(this.gaLabelAttr);
    }

    sendGARequest (gaItem) {
        ga(
            'send',
            gaItem.hitType,
            gaItem.eventCategory,
            gaItem.eventAction,
            gaItem.eventLabel,
            gaItem.transport
        );
    }

    rewriteDataAttributes (parentElement, childElement) {
        childElement.setAttribute(this.gaDataAttr, this.getGAAction(parentElement));
        childElement.setAttribute(this.gaLabelAttr, this.getGALabel(parentElement));

        return childElement;
    }

    init () {
        const actions = document.querySelectorAll(`[${this.gaDataAttr}]`);

        actions.forEach(action => {
            action = this.rewriteByChildElement(action);
            action.addEventListener('click', (event) => {
                this.sendGARequest(this.getGAItemObject(event.currentTarget));
            });
        });
    }
}

if ('undefined' !== typeof module) {
    module.exports = GoogleAnalyticsService;
}
