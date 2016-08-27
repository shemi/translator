<template xmlns:v-bind="http://www.w3.org/1999/xhtml">

    <div class="statuses clearfix">

        <div v-bind:class="statusClasses(locale.code)"
              v-for="locale in locales"
              v-bind:style="{width: 100 / locales.length + '%'}"
        >
            <span class="locale">{{ locale.code }}</span>
            <span class="translation">{{ localeValue(locale.code) }}</span>
        </div>

    </div>

</template>


<script type="text/ecmascript-6">



    export default {

        props: {
            'group': {
                required: true,
                twoWay  : false
            }
        },

        data(){
            return {
                indexes: {},
                locales: window.translator.locales
            }
        },

        methods: {
            getTranslationByLocale(locale) {

                if(! locale) {
                    return;
                }

                var translation = this.translations[locale];

                if(! translation) {
                    var position = this.group.length;

                    this.group.push({
                        value: '',
                        key: this.group[0].key,
                        group: this.group[0].group,
                        status: 2,
                        locale: locale,
                        isNew: true
                    });

                    translation = this.group[position];
                }

                return translation;
            },

            localeValue(code) {
                let trans = this.getTranslationByLocale(code);

                return trans ? trans.value : "";
            },

            localeStatus(code) {
                let trans = this.getTranslationByLocale(code);

                if(! trans) {
                    return;
                }

                switch (trans.status) {
                    case 0:
                        return "saved";
                        break;
                    case 1:
                        return "changed";
                        break;
                    case 2:
                        return "new";
                        break;
                    default:
                        return "none";
                        break;
                }

            },

            statusClasses(code) {
                let classes = {
                    'status': true
                };

                classes['status__' + this.localeStatus(code)] = true;

                return classes;
            }
        },

        computed: {
            transValue() {

            },
            translations() {
                return _.keyBy(this.group, 'locale');
            }
        },

        components: {

        }
    }
</script>