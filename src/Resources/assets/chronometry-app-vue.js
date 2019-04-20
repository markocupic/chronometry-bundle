var chronometryApp = new Vue({
    el: '#chronometry-app',
    data: {
        currentTime: '',
        runners: null,
        modal: {
            runnerIndex: null,
            arrived: false,
            title: '',
            fullname: '',
            runnerId: null,
            runnerIsFinisher: false,
            lastChange: '',
            endTime: '',
            hasAbandoned: false
        },
        searchForm: {
            showNumberDropdown: false,
            numberSuggests: [],
            showNameDropdown: false,
            nameSuggests: [],
        },
        stats: {
            total: 0,
            dispensed: 0,
            arrived: 0,
            running: 0,
            abandoned: 0,
            runnerstotal: 0,
        }
    },
    created: function () {
        let self = this;
        window.setInterval(function () {
            self.setTime();
        }, 1000);
        self.getDataAll();
        $(document).ready(function () {
            // Make table sortable
            $('#startlistTable').stupidtable();
        });
    },
    methods: {

        /**
         * Get all rows from server
         */
        getDataAll: function () {
            let self = this;
            let xhr = $.ajax({
                url: window.location.href,
                type: 'get',
                dataType: 'json',
                data: {
                    'ajaxRequest': 'true',
                    'getAll': 'true'
                }
            });
            xhr.done(function (response) {
                console.log(response);
                self.runners = response.data;
                self.stats = response.stats;
            });
            xhr.fail(function () {
                alert("XHR-Request fehlgeschlagen!!!");
            });
            xhr.always(function () {
                //
            });
        },
        /**
         * Open modal
         * @param index
         */
        openModal: function (index) {
            let self = this;
            let runner = self.runners[index];
            let modal = self.modal;

            modal.runnerIndex = index;
            modal.arrived = runner.endtime != '' ? true : false;
            modal.title = 'Startnummer ' + runner.number;
            modal.fullname = runner.fullname;
            modal.runnerIsFinisher = runner.endtime != '' ? true : false;
            modal.runnerId = runner.id;

            let d = new Date(runner.tstamp * 1000);
            modal.lastChange = 'Letzte Änderung: ' + self.getFormatedTime(d);

            modal.endTime = runner.endtime == '' ? self.getFormatedTime(new Date()) : runner.endtime;
            modal.endTime = runner.aufgegeben ? '' : modal.endTime;

            // If athlete has abandoned the race
            modal.hasAbandoned = runner.aufgegeben ? true : false;

            // Open modal
            $('.modal').modal({});

            // Get Focus on the Input Field
            $('.modal ').on('shown.bs.modal', function (e) {
                $('#endtimeCtrl').focus();
                showTime($('#clockDisplay'));
            });

            // Get Focus on the Input Field
            $('.modal ').on('hidden.bs.modal', function (e) {
                $('html, body').animate({
                        //scrollTop: $('body').offset().top
                    }, 500, function () {
                        $('#searchNumber').val('').focus();
                    }
                );
            });

            // Clear Input Field
            $("#inputClear").click(function () {
                modal.endTime = '';
            });
        },
        /**
         * Save data to server
         * @param index
         */
        saveData: function (index) {
            let self = this;
            let runner = self.runners[index];
            let modal = self.modal;


            var id = modal.runnerId;
            var endtime = $('#endtimeCtrl').val();
            var aufgegeben = $('.modal #aufgegebenCtrl').is(':checked') ? 1 : '';
            // Check for a valid input f.ex. 22:12:59
            var regex = /^(([0|1][0-9])|([2][0-3])):([0-5][0-9]):([0-5][0-9])$/;

            if (regex.test(endtime) || endtime == '') {

                // Close modal and fîre xhr
                $('.modal').modal('hide');

                // Xhr
                runner.requesting = true;
                let xhr = $.ajax({
                    url: window.location.href,
                    type: 'get',
                    dataType: 'json',
                    data: {
                        'ajaxRequest': 'true',
                        'saveRow': 'true',
                        'id': id,
                        'index': index,
                        'endtime': endtime,
                        'aufgegeben': aufgegeben
                    }
                });
                xhr.done(function (response) {

                    if (response.status == 'success') {
                        runner.requesting = false;
                        self.runners[index] = response.data;
                        self.stats = response.stats;
                    } else {
                        alert('Fehler');
                    }
                });
                xhr.fail(function () {
                    alert("XHR-Request für id " + id + " fehlgeschlagen!!!");
                });
                xhr.always(function () {
                    runner.requesting = false;
                });

            } else {
                alert('Ungültige Eingabe: ' + endtime);
            }
        },

        /**
         * Set current time
         */
        setTime: function () {

            var currentTime = new Date();
            var h = currentTime.getHours();
            var m = currentTime.getMinutes();
            var s = currentTime.getSeconds();
            if (h < 10) {
                h = '0' + h;
            }
            if (m < 10) {
                m = '0' + m;
            }
            if (s < 10) {
                s = '0' + s;
            }

            this.currentTime = h + ":" + m + ":" + s;
        },

        /**
         * Set end time from current time
         * @param index
         */
        setEndTimeFromCurrentTime: function (index) {
            let self = this;
            //let runner = self.runners[index];
            let modal = self.modal;

            var d = new Date();
            var formatedTime = self.getFormatedTime(d);
            modal.endTime = formatedTime;
        },

        /**
         * Clear end time
         * @param index
         */
        clearEndTime: function (index) {
            let self = this;
            //let runner = self.runners[index];
            let modal = self.modal;
            modal.endTime = '';
        },

        /**
         * Show number dropdown
         * @param event
         */
        showNumberDropdownSuggest: function (event) {
            let self = this;
            let dropdown = $('#searchNumberDropdown');
            let input = event.target;

            if ($(input).val() == '') {
                return;
            }

            var rows = $('#startlistTable tbody tr');

            var regex = new RegExp('^' + $(input).val() + '(.*)', 'i');

            self.searchForm.numberSuggests = [];
            rows.each(function () {
                if (regex.test($(this).attr('data-number'))) {

                    let runner = {
                        index: $(this).attr('data-index'),
                        number: $(this).attr('data-number'),
                        fullname: $(this).attr('data-fullname')
                    };

                    self.searchForm.numberSuggests.push(runner);
                    self.searchForm.showNumberDropdown = true;
                }
            });
        },

        /**
         * Remove number dropdown
         * @param event
         */
        removeNumberDropdownSuggest: function (event) {
            let self = this;
            let input = event.target;
            window.setTimeout(function () {
                $(input).val('');
                self.searchForm.numberSuggests = [];
                self.searchForm.showNumberDropdown = false;
            }, 200);
        },

        /**
         * Show number dropdown
         * @param event
         */
        showNameDropdownSuggest: function (event) {
            let self = this;
            let dropdown = $('#searchNameDropdown');
            let input = event.target;

            if ($(input).val() == '') {
                return;
            }

            var rows = $('#startlistTable tbody tr');

            var regex = new RegExp($(input).val() + '(.*)', 'i');

            self.searchForm.nameSuggests = [];
            rows.each(function () {
                if (regex.test($(this).attr('data-fullname'))) {

                    let runner = {
                        index: $(this).attr('data-index'),
                        number: $(this).attr('data-number'),
                        fullname: $(this).attr('data-fullname')
                    };

                    self.searchForm.nameSuggests.push(runner);
                    self.searchForm.showNameDropdown = true;
                }
            });
        },

        /**
         * Remove name dropdown
         * @param event
         */
        removeNameDropdownSuggest: function (event) {
            let self = this;
            let input = event.target;
            window.setTimeout(function () {
                $(input).val('');
                self.searchForm.nameSuggests = [];
                self.searchForm.showNameDropdown = false;
            }, 200);
        },

        /**
         * Scroll to number
         * @param event
         */
        scrollToNumber: function (event) {
            let self = this;
            let input = event.target;
            if ($(input).val() > 1) {
                var tr = $("tr[data-number='" + $(input).val() + "']");
                if ($(tr).length) {
                    $('html, body').animate({
                            scrollTop: $(tr).offset().top - 40
                        }, 500, function () {
                            //Open modal
                            let index = $(tr).data('index');
                            self.openModal(index);
                        }
                    );
                }
            }
        },
        /**
         * Toggle sidebar
         */
        toggleSidebar: function () {
            $('#sidebarContainer .inner').toggle(500, function () {
                $('#sidebarContainer').toggleClass('hidden-sidebar');
            });
        },

        /**
         * Apply filter
         * @param event
         */
        applyFilter: function (event) {
            let select = event.target;
            // Filter option
            var $filterCat = $(select).val();
            var rows = $('.startlist-table tbody tr');
            rows.removeClass('invisible');

            if ($filterCat == 0) return;

            rows.each(function () {
                if ($(this).attr('data-category') != $filterCat) {
                    $(this).addClass('invisible');
                }
            });
        },

        /**
         * Get formated time
         * @param d
         * @returns {string}
         */
        getFormatedTime: function (d) {
            let hours = d.getHours() < 10 ? '0' + d.getHours() : d.getHours();
            let minutes = d.getMinutes() < 10 ? '0' + d.getMinutes() : d.getMinutes();
            let seconds = d.getSeconds() < 10 ? '0' + d.getSeconds() : d.getSeconds();
            return hours + ":" + minutes + ":" + seconds;
        }
    }
});
