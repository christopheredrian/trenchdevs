<template>
    <div class="card mb-4">
        <div class="card-header">
            Markdown Notes
        </div>
        <div class="card-body">

            <div class="row">
                <div class="col">
                    <div class="form-group">
                        <label for="title">Title</label>
                        <input
                            v-model="title"
                            type="text"
                            class="form-control"
                            id="title"
                            placeholder="Enter Title"
                            required
                        >
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <textarea class="form-control w-100 h-100 mh-100" v-model="markdown"/>
                </div>
                <div class="col-md-6">
                    <div v-html="html"></div>
                </div>
            </div>

        </div>
    </div>
</template>

<script>
import axios from 'axios';
import {isEmpty, flatMap, flatten} from 'lodash';
import marked from 'marked';

export default {
    components: {},
    mounted() {
        console.log('component mounted...')
    },
    data() {
        return {
            title: '',
            markdown: '',
            errors: null,
            emails: null,
        };
    },
    computed: {
        html: function() {
            return marked(this.markdown);
        },
    },
    methods: {
        announce() {
            const data = {
                title: this.title,
                message: this.message,
                emails: this.emails || null,
            };

            axios.post('/portal/announcements/announce', data)
                .then(({data = {}}) => {

                    const {status, message} = data;

                    if (status === 'success') {
                        window.location.href = "/portal/announcements"
                    } else {
                        if (message) {
                            alert(message);
                        }
                    }


                })
                .catch((e) => {
                    const {response = {}} = e;

                    if (!isEmpty(response)) {
                        const {message, errors = []} = response.data;

                        if (!isEmpty(errors)) {
                            this.errors = flatten(flatMap(errors));
                        }
                    }
                });
        }
    }
}
</script>
