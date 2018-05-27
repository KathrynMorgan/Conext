<template>
  <div>
    <v-alert outline type="error" :value="error">
      {{ error }}
    </v-alert>
    
    <p>Test the email template is working by sending a real email message.</p>
    
    <v-form ref="form" v-model="valid" lazy>
      <h3>Recipient</h3><v-text-field v-model="to" label="To:" :rules="emailRules" hint="Enter the recipients email address."></v-text-field>
  
      <div v-if="parameters.length > 0">
        <h3>Paramiters</h3>
        <p>The following parameters were matched from the templates source, they should be sent in the POST request when sending from your application.</p>
        <v-text-field v-for="parameter in parameters" :key="parameter.key" v-model="parameter.value" :label="parameter.key" hint="Enter value for parameter."></v-text-field>
      </div>    
    </v-form>

    <h3>Example Codes</h3>
    <p>Below are some code snippets, which show how you would send the parameters in a POST request from your application.</p>
    
    <h4>Javascript (axios)</h4>
    <no-ssr placeholder="Loading...">
      <codemirror v-model="exampleJs" :options="cmOption" class="codeExamplesJS"></codemirror>
    </no-ssr>
    
    <br>
    <h4>PHP (curl)</h4>
    <no-ssr placeholder="Loading...">
      <codemirror v-model="examplePHP" :options="cmOption" class="codeExamplesPHP"></codemirror>
    </no-ssr>
  </div>
</template>

<script>
  import { mapGetters } from 'vuex'
  import axios from 'axios'

  export default {
    components: {},
    props: ['item'],
    computed: {
      ...mapGetters({
        loggedUser: 'auth/loggedUser',
        loggedToken: 'auth/loggedToken'
      }),
      axiosParams () {
        var ret = {}
        ret['to'] = this.to
        this.parameters.forEach(value => {
          ret[value.key] = value.value
        })
        return ret
      },
      curlParams () {
        var ret = ''
        ret += '\'to\' => \''+this.to+'\',\n';
        this.parameters.forEach(value => {
          ret += '    \''+value.key+'\' => \''+value.value+'\',\n';
        })
        return ret
      },
      exampleJs () {
        return `axios.post('`+this.loggedUser.sub+`/api/email/`+this.template.slug+`', `+JSON.stringify(this.axiosParams, null, 2)+`).then(response => {
  console.log(response);
}).catch(error => {
  console.log(error);
});`
      },
      examplePHP () {
        return `$ch = curl_init('`+this.loggedUser.sub+`/api/email/`+this.template.slug+`');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, [
    `+this.curlParams+`]);

$response = curl_exec($ch);
curl_close($ch);`
      }
    },
    data: () => ({
      // code mirror options
      cmOption: {
        smartIndent: false,
        indentWithTabs: true,
        tabSize: 4,
        indentUnit:4,
        foldGutter: true,
        styleActiveLine: true,
        lineNumbers: false,
        line: true,
        readOnly: true,
        keyMap: "sublime",
        mode: 'text/x-php'
      },
      // global error
      error: '',
      valid: true,
      template: {},
      to: '',
      parameters: [],
      emailRules: [
        v => {
          return !!v || 'E-mail is required'
        },
        v => /^\w+([.-]?\w+)*@\w+([.-]?\w+)*(\.\w{2,})+$/.test(v) || 'E-mail must be valid'
      ]
    }),
    beforeDestroy: function() {},
    mounted: function () {
      this.template = Object.assign({}, this.item)
      
      if (this.item.id !== -1) {
        this.initialize()
      }
    },
    methods: {
      async initialize () {
        
        this.to = this.$storage.get("test_email") || ''

        // template send key
        if (this.template.key !== '') {
          this.parameters.push({ key: 'key', value: this.template.key, required: true });
        }

        // regex out parameter placeholders from subject
        let subject = this.template.subject.match(/\{\{[ ]{0,}([\w\_-]{1,})[ ]{0,}\}\}/gi);
        if (subject) {
          subject.forEach(value => {
            this.parameters.push({ key: value.replace('{{','').replace('}}','').trim(), value: '', required: false });
          })
        }
        // regex out parameter placeholders from source
        let source = this.template.source.match(/\{\{[ ]{0,}([\w\_-]{1,})[ ]{0,}\}\}/gi);
        if (source) {
          source.forEach(value => {
            this.parameters.push({ key: value.replace('{{','').replace('}}','').trim(), value: '', required: false });
          })
        }
      },
      
      async send() {
        if (this.$refs.form.validate()) {
          axios.post(this.loggedUser.sub + '/api/email/'+this.template.slug, this.axiosParams).then(response => {
            if (response.data.error) {
              this.error = response.data.error + '!'
            } else {
              this.$storage.set("test_email", this.axiosParams.to)
              this.$emit('snackbar', 'Test email sent!')
            }
          }).catch(error => {
            this.error = 'Test email failed to send!'
          });
        }
      }
      
    }
  }
</script>

<style>
  .codeExamplesJS .CodeMirror  {
    border: 1px solid #eee;
    height:190px !important;
    min-height:190px !important;
    font-family: Ubuntu Mono, Menlo, Consolas, monospace;
    font-size: 13px;
    line-height:1.1em;
  }
  .codeExamplesJS .CodeMirror-scroll{
    height:190px !important;
    min-height:190px !important;
  }
  .codeExamplesPHP .CodeMirror  {
    border: 1px solid #eee;
    height:255px !important;
    min-height:255px !important;
    font-family: Ubuntu Mono, Menlo, Consolas, monospace;
    font-size: 13px;
    line-height:1.1em;
  }
  .codeExamplesPHP .CodeMirror-scroll{
    height:255px !important;
    min-height:255px !important;
  }
</style>
