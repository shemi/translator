const ImportGroupsMixin = {

    methods: {
        importGroups: function(replace) {
            this.$dispatch('main-loading-start', 'Importing groups...');

            this.$http.post('import', {replace}).then(
                function(res) {
                    var data = res.json();

                    this.$emit('groups-imported');

                    this.$dispatch('main-loading-stop');

                    this.$dispatch('main-alert', true, {
                        title: 'Yeah!!',
                        description: data.counter + ' groups imported'
                    });

                },
                function(err) {
                    this.$dispatch('main-loading-stop');
                }
            );
        }
    }
};

export default ImportGroupsMixin;