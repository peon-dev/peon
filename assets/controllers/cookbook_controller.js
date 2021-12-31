import { Controller } from "stimulus"
import 'highlight.js/styles/github.css';

import highlight from 'highlight.js/lib/core';
import diff from 'highlight.js/lib/languages/diff';

export default class extends Controller {
    connect() {
        highlight.registerLanguage('diff', diff);
        highlight.highlightAll();
    }
}



