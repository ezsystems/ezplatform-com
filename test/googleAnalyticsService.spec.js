const expect = require('chai').use(require('chai-dom')).use(require('chai-subset')).expect;
const sinon = require('sinon');
const { JSDOM } = require('jsdom');
const GoogleAnalyticsService = require('../web/assets/js/GoogleAnalyticsService');

describe('GoogleAnalyticsService', () => {
    let gaService;
    let document;
    let handleClick;

    const template = '/html/gaTemplate.html';
    const gaDataAttr = 'data-ga-action';
    const gaCategoryAttr = 'data-ga-category';
    const gaLabelAttr = 'data-ga-label';

    beforeEach(() => {
        gaService = new GoogleAnalyticsService();
        handleClick = sinon.spy();

        return JSDOM.fromFile(__dirname + template)
            .then(dom => {
                document = dom.window.document;
                document.addEventListener('click', handleClick, false);

                return document;
            })
            .catch(error => {
                console.log(error);
            });
    });

    it('should return instance of GoogleAnalyticsService', () => {
        expect(gaService).to.be.instanceOf(GoogleAnalyticsService);
    });

    it('should find selectors with data attribute data-ga-action', () => {
        expect(document.querySelectorAll(`[${gaDataAttr}]`)).not.to.have.lengthOf(0);
    });

    it('should return value for each data-ga-action attribute', () => {
        const actions = document.querySelectorAll(`[${gaDataAttr}]`);

        actions.forEach(action => {
            expect(gaService.getGAAction(action), action.outerHTML).not.to.have.lengthOf(0);
        });
    });

    it('should return true if body has data-ga-category attribute', () => {
        expect(document.body).to.have.attribute(gaCategoryAttr);
    });

    it('should return value for data-ga-category attribute', () => {
        expect(document.querySelector(`[${gaCategoryAttr}]`).getAttribute(gaCategoryAttr)).not.to.have.lengthOf(0);
    });

    it('should return Google Analytics Label if data-ga-label attribute exists', () => {
        const labels = document.querySelectorAll(`[${gaLabelAttr}]`);

        labels.forEach(label => {
            expect(gaService.getGALabel(label), label.outerHTML).not.to.have.lengthOf(0);
        });
    });

    it('should rewrite data attributes to child element when ga-link-listener class exists', () => {
        const element = document.querySelector('.ga-link-listener');
        const childElement = gaService.rewriteByChildElement(element);

        expect(childElement.dataset).containSubset(element.dataset);
    });

    it('should create object after click on data-ga-action event', () => {
        const actions = document.querySelectorAll(`[${gaDataAttr}]`);
        let gaItem;

        actions.forEach(action => {
            action.addEventListener('click', () => {
                gaItem = new Object({
                    hitType: 'event',
                    eventCategory: 'Downloads',
                    eventAction: 'Download',
                    eventLabel: 'Label',
                    transport: 'Transport'
                });
            });
            action.click();
            expect(gaItem).to.be.an('object').that.containSubset({
                'hitType': 'event',
                'eventCategory': 'Downloads',
                'eventAction': 'Download',
                'eventLabel': 'Label',
                'transport': 'Transport',
            });
        });

        expect(handleClick.callCount).to.equal(actions.length);
    });
});
