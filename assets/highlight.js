import 'highlight.js/styles/github.css';

import highlight from 'highlight.js/lib/core';
import diff from 'highlight.js/lib/languages/diff';

highlight.registerLanguage('diff', diff);
highlight.highlightAll();
