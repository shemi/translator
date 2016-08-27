var zenscroll = require('zenscroll');

let ScrollToSelected = {

    update(newSelected, oldSelected) {
        let $el = this.el;

        if (!newSelected) {
            return;
        }

        Vue.nextTick(function () {
            let defaultDuration = 50,
                edgeOffset      = 0,
                scroller        = zenscroll.createScroller($el, defaultDuration, edgeOffset),
                target          = document.getElementById(newSelected);

            if(! target) {
                return;
            }

            scroller.center(target);
        });

    }

};

export default ScrollToSelected;