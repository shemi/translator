const GetGroupsMixin = {

    data: function() {
        return {
            gettingGroups: false
        }
    },

    methods: {
        getGroups() {

            this.gettingGroups = true;

            this.$http
                .get('groups')
                .then(
                    function(res) {
                        this.groups = res.json();
                        this.gettingGroups = false;
                    },
                    function(err) {
                        this.gettingGroups = false;
                    }
                )
        }
    }
};

export default GetGroupsMixin;