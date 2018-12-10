var that = this;
var result = {

    componentInit: function() {
        /**
         * If the question is in a readonly state, e.g. after being
         * answered or in the review page then stop any further
         * selections.
         *
         * @param {NodeList} draggables
         * @param {MouseEvent} event
         * @return {string} value of target
         **/
        if (!this.question) {
            //console.warn('Aborting because of no question received.');
            return that.CoreQuestionHelperProvider.showComponentError(that.onAbort);
        }
        const div = document.createElement('div');
        div.innerHTML = this.question.html;
         // Get question questiontext.
        const questiontext = div.querySelector('.qtext');
         // Get question input.
        const input = div.querySelector('input[type="text"][name*=answer]');

        // Replace Moodle's correct/incorrect and feedback classes with our own.
        this.CoreQuestionHelperProvider.replaceCorrectnessClasses(div);
        this.CoreQuestionHelperProvider.replaceFeedbackClasses(div);

        if (div.querySelector('.readonly') !== null) {
            this.question.readonly = true;
        }
        if (div.querySelector('.feedback') !== null) {
            this.question.feedback = div.querySelector('.feedback');
            this.question.feedbackHTML = true;
        }

        this.question.text = questiontext.innerHTML;
        this.question.input = input;
        if (typeof this.question.text == 'undefined') {
            //this.logger.warn('Aborting because of an error parsing question.', this.question.name);
            return this.CoreQuestionHelperProvider.showComponentError(this.onAbort);
        }

        // Wait for the DOM to be rendered.
        setTimeout(() => {

        });
        return true;
    }
};
result;