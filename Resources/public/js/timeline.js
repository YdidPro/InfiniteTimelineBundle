class Timeline {
    focusElem = null;
    _focusDate = null;
    _listeners = [];
    centeredElem = null;
    current = null;
    previous = null;
    next = null;
    timelineOpts = null;
    timelineEvent = null;
    firstCall = true;

    constructor(focusDate) {
        
        this.timelineOpts = new TimelineOpts();
        this.timelineEvent = new TimelineEvent(this);
        this.timelineEvent.addStepListener();
        this.timelineEvent.addSearchListener();
        this._focusDate = focusDate;
        this._listeners = [];
    }

    get focusDate() {
        return this._focusDate;
    }
    
    set focusDate(newFocusDate) {
        this._focusDate = newFocusDate;
        this._notifyListeners();
    }

    getStep() {
        return this.timelineOpts.step;
    }
    
    _notifyListeners() {
        for (const listener of this._listeners) {
            listener(this._focusDate);
        }
    }

    addListener(listener) {
        this._listeners.push(listener);
    }

    generatePreviousTimeline() {
        var xhr = new XMLHttpRequest();
        var params = {
            'current': this.current,
            'previous': this.previous,
            'next': this.next,
            'step': this.timelineOpts.getStep(),
        };
        var paramsJSON = JSON.stringify(params);
        var self = this;
        xhr.open('POST', "/ydid/infinite-timeline/generatePreviousTimeline", true);
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.send(paramsJSON);

        xhr.onreadystatechange = function () {
            if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
                const timeline = document.getElementById('timeline');
                const nextTimeline = document.querySelector('.nextTimeline');
                nextTimeline.remove();

                const currentTimeline = document.querySelector('.currentTimeline');
                currentTimeline.classList.remove('currentTimeline');
                currentTimeline.classList.add('nextTimeline');

                const previousTimeline = document.querySelector('.previousTimeline');
                previousTimeline.classList.remove('previousTimeline');
                previousTimeline.classList.add('currentTimeline');

                let newPreviousTimeline = document.createElement('div');
                newPreviousTimeline.classList.add('previousTimeline');
                newPreviousTimeline.classList.add('timelines');

                const response = JSON.parse(this.responseText);
                newPreviousTimeline.innerHTML = response.previousTimelineHtml;

                timeline.prepend(newPreviousTimeline);

                self.current = response.current;
                self.previous = response.previous;
                self.next = response.next;
                self.timelineEvent.scrollToElement(self.current.start, 'auto', 'start');
                self.timelineEvent.addClickListener();
            }
        };
    }

    generateNextTimeline() {
        var xhr = new XMLHttpRequest();
        var params = {
            'current': this.current,
            'previous': this.previous,
            'next': this.next,
            'step': this.timelineOpts.getStep(),
        };
        var paramsJSON = JSON.stringify(params);
        var self = this;
        xhr.open('POST', "/ydid/infinite-timeline/generateNextTimeline", true);
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.send(paramsJSON);

        xhr.onreadystatechange = function () {
            if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
                const timeline = document.getElementById('timeline');

                const previousTimeline = document.querySelector('.previousTimeline');
                previousTimeline.remove();

                
                
                const currentTimeline = document.querySelector('.currentTimeline');
                currentTimeline.classList.remove('currentTimeline');
                currentTimeline.classList.add('previousTimeline');
                
                const nextTimeline = document.querySelector('.nextTimeline');
                nextTimeline.classList.remove('nextTimeline');
                nextTimeline.classList.add('currentTimeline');

                let newNextTimeline = document.createElement('div');
                newNextTimeline.classList.add('nextTimeline');
                newNextTimeline.classList.add('timelines');

                const response = JSON.parse(this.responseText);
                newNextTimeline.innerHTML = response.nextTimelineHtml;

                timeline.append(newNextTimeline);

                self.current = response.current;
                self.previous = response.previous;
                self.next = response.next;

                self.timelineEvent.scrollToElement(self.current.end, 'auto', 'end');
                self.timelineEvent.addClickListener();
            }
        };
    }

    setFocus(classDate) {
        if(this.focusElem != null) {
            this.focusElem.classList.remove('focus');
        }
        this.focusElem = document.getElementById(classDate);
        this.focusElem.classList.add('focus');
        this.focusDate = this.focusElem.dataset.date;
    }

    elemClicked(elem) {
        this.setFocus(elem.id);
    }

    setCenteredElem() {

        const searchInput = document.getElementById('searchInput');
        if(searchInput.value != '' && this.verifySearch(searchInput.value)) {
            this.centeredElem = searchInput.value
        } else {
            // Sélectionnez tous les éléments avec la classe 'elem'
             const elemElements = document.querySelectorAll('.elem');
     
             // Calcule le centre horizontal de l'écran
             const centerX = window.innerWidth / 2 + window.scrollX;
     
             let closestElem = null;
             let minDistance = Infinity;
     
             // Parcourez les éléments avec la classe 'elem' pour trouver le plus proche du centre
             elemElements.forEach((elem) => {
                 const elemLeft = elem.getBoundingClientRect().left + elem.offsetWidth / 2;
                 const distance = Math.abs(centerX - elemLeft);
     
                 if (distance < minDistance) {
                     minDistance = distance;
                     closestElem = elem;
                 }
             });
     
             // Maintenant 'closestElem' contient l'élément le plus proche du centre horizontal de l'écran
             this.centeredElem = closestElem.id;
        }

    }

    generateTimeLine() {
        var xhr = new XMLHttpRequest();
        var params = {
            step : this.timelineOpts.getStep(),
            centeredYear : this.centeredElem,
        };
        var paramsJSON = JSON.stringify(params);
        var self = this;
        xhr.open('POST', "/ydid/infinite-timeline/generateTimeLine", true);
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.send(paramsJSON);

        xhr.onreadystatechange = function () {
            if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
                const currentTimeline = document.querySelector('.currentTimeline');
                const previousTimeline = document.querySelector('.previousTimeline');
                const nextTimeline = document.querySelector('.nextTimeline');

                const response = JSON.parse(this.responseText);
                currentTimeline.innerHTML = response.currentTimelineHtml;
                previousTimeline.innerHTML = response.previousTimelineHtml;
                nextTimeline.innerHTML = response.nextTimelineHtml;

                self.current = response.current;
                self.previous = response.previous;
                self.next = response.next;

                self.timelineEvent.addClickListener();
                self.centeredElem = response.focus;

                if(self.firstCall) {
                    self.firstCall = false;
                    self.timelineEvent.addOverListener();
                    self.setFocus(response.focus);
                }
                
                self.timelineEvent.scrollToElement(self.centeredElem, 'auto', 'center');
            }
        };
    }

    verifySearch(dateInput) {
        let regex = /^(?:(0[1-9]|[12]\d|3[01])[-\/](0[1-9]|1[0-2])[-\/](\d{4}))|^(0[1-9]|1[0-2])[-\/](\d{4})|^(\d{4})$/;

        if(regex.test(dateInput)) {
            console.log('correct ' + dateInput);
            return true;
        } else {
            console.log('incorrect ' + dateInput);
            return false;
        }
    }
}

class TimelineEvent {

    timelineClass = null;

    constructor(timelineClass) {
        this.timelineClass = timelineClass;
    }

    addClickListener() {
        const allElem = document.querySelectorAll('.elem');
        allElem.forEach((elem) => {
            elem.addEventListener('click', () => this.timelineClass.elemClicked(elem));
        })
    }

    addSearchListener() {
        const searchInput = document.getElementById('searchInput');
        searchInput.addEventListener('keyup', (elem) => {
            let value = elem.currentTarget.value;
            if(this.timelineClass.verifySearch(value))
            {
                this.timelineClass.centeredElem = value; 
                this.timelineClass.generateTimeLine();
            }
        });
    }

    addOverListener() {
        const timeline = document.getElementById('timeline');
        timeline.addEventListener('scroll', () => {
            const timelineWidth = timeline.getBoundingClientRect().width;

            if (timeline.scrollLeft === 0) {
                this.timelineClass.generatePreviousTimeline();
            }

            if (timeline.scrollLeft + timelineWidth >= timeline.scrollWidth) {
                this.timelineClass.generateNextTimeline();
            }
        })
    }

    addStepListener() {
        const rangeTimeline = document.getElementById('rangeTimeline');
        const self = this;
        rangeTimeline.addEventListener('change', (elem) => {
            const rangeLabel = document.querySelector('.rangeLabel');
            let stepOpts = this.timelineClass.timelineOpts.stepOpts[elem.currentTarget.value];
            this.timelineClass.timelineOpts.setStep(stepOpts.value);
            rangeLabel.innerHTML = stepOpts.label;
            this.timelineClass.setCenteredElem();
            this.timelineClass.generateTimeLine();
        })
    }

    scrollToElement(classDate, type, position)
    {
        const elem = document.getElementById(classDate);
        console.log(classDate);
        elem.scrollIntoView({
            behavior: type, // Utilisez 'auto' pour un défilement instantané
            block: position,     // 'start', 'center', 'end', ou 'nearest'
            inline: position   // 'start', 'center', 'end', ou 'nearest'
        });
    }
}


class TimelineOpts {

    step = 10;
    search = null;
    size = 1;

    stepOpts = [
        {value: 0.5, label: 'jours'},
        {value: 1, label: 'mois'},
        {value: 10, label: '1 an'},
        {value: 100, label: '10 ans'},
        {value: 1000, label: '100 ans'},
        {value: 10000, label: '1000 ans'},
        {value: 100000, label: '10 000 ans'},
        {value: 1000000, label: '100 000 ans'},
        {value: 10000000, label: '1 000 000 années'}
    ];

    constructor() {

    }

    setStep(step) {
        this.step = step;
    }

    getStep() {
        return this.step;
    }

    setSearch(search) {
        this.search = search;
    }

    getSearch() {
        return this.search;
    }

    setSize(size) {
        this.size = size;
    }

    getSize() {
        return this.size;
    }

    reset() {
        this.step = 10;
        this.search = null;
        this.size = 1;
    }
}