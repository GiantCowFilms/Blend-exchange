<script>
import blendExchange from '@/Api/BlendExchangeApi'
function resetUploadProgress (form) {
    Object.keys(form.uploadProgress).forEach(key => {
        form.uploadProgress[key] = 0;
    });        
};
export default {
    methods: {
        setFile(form,files,name,input) {
            this.$set(files,name,input[0]);
            this.$set(form.uploadProgress,name,0);
        },
        setAuthentication(authentication) {
            this.form.auth = authentication;
        },
        setEndpoint(endpoint) {
            this.form.endpoint = endpoint;
        },
        async submit(data,files,form) {
            // Do not start uploads while an upload is pending.
            if (form.status === 'pending') {
                return;
            }
            if(form.endpoint === null) {
                throw new TypeError('endpoint cannot be null. Set with setEndpoint()');
            }
            form.status = 'pending';
            form.errors = {};
            form.serverError = undefined;

            let filesMeta = {};
            data['fileNames'] = {};
            for(let file in files) {
                data['fileNames'][file] = files[file].name
            }
            try {
                let result = await blendExchange.submitForm({
                    submissionData: data,
                    metaData: {
                        token: form.auth,
                    },
                    submissionFiles: files,
                    endpoint: form.endpoint,
                    options: {
                        fileUploadProgress (key,pe) {
                            let progress =  (pe.loaded/pe.total) * 100; 
                            form.uploadProgress[key] = progress;
                            console.log(`Upload ${progress}% complete.`);
                        }
                    }
                });
                form.status = 'completed';
                resetUploadProgress(form);
                this.completedAjaxUpload(result);
                return result;
            } catch (err) {
                form.status = 'failed';
                resetUploadProgress(form);
                if (typeof err.response === 'undefined') {
                    throw err;
                }
                if (err.response.status !== 422) {
                    if (err.response.data.type === 'error') {
                        form.serverError = err.response.data.error;
                    } else {
                        form.serverError = 'There was an internal error attempting to complete your request.';
                    }

                } else {
                    form.serverError = err.response.data.error;
                    form.errors = err.response.data.errors;
                }
            }
        }
    },
    data () {
        return {
            files: {},
            form: {
                errors: {},
                uploadProgress: {},
                status: '',
                endpoint: null,
                serverError: null
            }
        }
    }
}
</script>
