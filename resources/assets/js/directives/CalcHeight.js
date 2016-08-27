
let CalcHeight = {

    offsetTop: 0,

    handler: undefined,

    bind: function () {

        this.handler = function () {
            var offsetTop = this.el.offsetTop;

            if (this.offsetTop !== offsetTop) {
                this.offsetTop = offsetTop;
                this.setHeight();
            }

        }.bind(this);

        window.addEventListener('resize', this.handler);
        this.el.addEventListener('DOMSubtreeModified', this.handler);

    },

    setHeight() {
        let $el = this.el;

        $el.style.height = '100px';

        Vue.nextTick(function() {
            let windowHeight = window.innerHeight,
                offsetTop = $el.getBoundingClientRect().top + 25;

            $el.style.height = (windowHeight - offsetTop) + 'px';
        });
    }

};

export default CalcHeight;