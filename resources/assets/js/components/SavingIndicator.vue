<template>
    <div class="saving-loader">
        <div v-show="saving && status != 'start'"
             class="saving__animation">
            <div class="loader__figure__holder">
                <div class="loader__figure"></div>
            </div>
            <p class="loader__label">{{ savingLabel }}</p>
        </div>

        <div v-show="status == 'saved'"
             class="saving__saved__label">
            <p class="text-primary">{{ savedLabel }}</p>
        </div>

    </div>
</template>


<script type="text/ecmascript-6">
    export default {
        props: {

        },

        data: () => {
            return {
                saving: false,
                saved: false,
                status: 'start',
                savingLabel: 'SAVING...',
                savedLabel: 'All changes saved.'
            }
        },

        methods: {
            start() {
                this.saving = true;
                this.saved = false;
                this.status = 'saving';
            },

            stop() {
                this.saving = true;
                this.saved = false;
                this.status = 'saved';
            },

            end() {
                this.saving = false;
                this.saved = true;
                this.status = 'saved';
            }
        }

    }
</script>