import Vue from 'vue'
import VueCodemirror from 'vue-codemirror'

// language
import 'codemirror/mode/php/php.js'

// active-line.js
import 'codemirror/addon/selection/active-line.js'

// styleSelectedText
import 'codemirror/addon/selection/mark-selection.js'
import 'codemirror/addon/search/searchcursor.js'

// highlightSelectionMatches
import 'codemirror/addon/scroll/annotatescrollbar.js'
import 'codemirror/addon/search/matchesonscrollbar.js'
import 'codemirror/addon/search/searchcursor.js'
import 'codemirror/addon/search/match-highlighter.js'

// keyMap
//import 'codemirror/mode/clike/clike.js'
//import 'codemirror/addon/edit/matchbrackets.js'
//import 'codemirror/addon/comment/comment.js'
//import 'codemirror/addon/dialog/dialog.js'
//import 'codemirror/addon/dialog/dialog.css'
import 'codemirror/addon/search/searchcursor.js'
import 'codemirror/addon/search/search.js'
import 'codemirror/keymap/sublime.js'

import 'codemirror/addon/display/autorefresh.js'
// foldGutter
import 'codemirror/addon/fold/foldgutter.css'
//import 'codemirror/addon/fold/brace-fold.js'
//import 'codemirror/addon/fold/comment-fold.js'
//import 'codemirror/addon/fold/foldcode.js'
//import 'codemirror/addon/fold/foldgutter.js'
//import 'codemirror/addon/fold/indent-fold.js'
//import 'codemirror/addon/fold/markdown-fold.js'
//import 'codemirror/addon/fold/xml-fold.js'

// more...

Vue.use(VueCodemirror, {
  options: {
    smartIndent: false,
    indentWithTabs: true,
    tabSize: 4,
    indentUnit: 4,
    //foldGutter: true,
    styleActiveLine: true,
    lineNumbers: true,
    //line: true,
    keyMap: "sublime"
  },
  events: ['refresh']
})
