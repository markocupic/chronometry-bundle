/**
 * Chronometry Module for Contao CMS
 * Copyright (c) 2008-2019 Marko Cupic
 * @package chronometry-bundle
 * @author Marko Cupic m.cupic@gmx.ch, 2019
 * @link https://github.com/markocupic/chronometry-bundle
 */
var chronometryApp = new Vue({
    el: '#chronometry-app',
    data: {
        isReady: false,
        isOnline: '',
        requestToken: '',
        currentTime: '',
        runners: null,
        categories: null,
        sidebar: {
            status: 'closed',
        },
        modal: {
            runnerIndex: null,
            runnerNumber: '',
            runnerFullname: '',
            runnerId: null,
            runnerIsFinisher: false,
            runnerdnf: false,
            lastChange: '',
            endTime: '',
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
            haveFinished: 0,
            running: 0,
            haveGivenUp: 0,
            runnersTotal: 0,
        }
    },
    created: function () {
        var self = this;
        self.requestToken = CHRONOMETRY.requestToken;

        window.setTimeout(function () {
            self.isReady = true;
        }, 2000);

        window.setInterval(function () {
            self.setTime();
        }, 1000);

        self.checkOnlineStatus();
        window.setInterval(function () {
            self.checkOnlineStatus();
        }, 15000);

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
            var self = this;
            var xhr = $.ajax({
                url: window.location.href,
                type: 'post',
                dataType: 'json',
                data: {
                    'REQUEST_TOKEN': self.requestToken,
                    'action': 'getDataAll'
                }
            });
            xhr.done(function (response) {
                self.runners = response.data;
                self.stats = response.stats;
                self.categories = response.categories;
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
            var self = this;
            var runner = self.runners[index];
            var modal = self.modal;

            modal.runnerIndex = index;
            modal.runnerNumber = runner.number;
            modal.runnerFullname = runner.fullname;
            modal.runnerIsFinisher = runner.endtime != '' ? true : false;
            modal.runnerHasNotice = runner.notice != '' ? true : false;
            modal.runnerNotice = runner.notice.replace(/\&\a\m\p\;/g, '&');

            modal.runnerId = runner.id;

            var d = new Date(runner.tstamp * 1000);
            modal.lastChange = 'letzte Änderung: ' + self.getFormatedTime(d);

            modal.endTime = runner.endtime == '' ? self.currentTime : runner.endtime;
            modal.endTime = runner.hasGivenUp ? '' : modal.endTime;

            // If runner has given up the race
            modal.runnerHasGivenUp = runner.hasGivenUp ? true : false;

            // Get Focus on the Input Field
            $('.modal ').on('hidden.bs.modal', function (e) {
                $('html, body').animate({
                        //scrollTop: $('body').offset().top
                    }, 50, function () {
                        $('#searchNumber').val('').focus();
                        $('#searchName').val('');
                        self.searchForm.numberSuggests = [];
                        self.searchForm.nameSuggests = [];
                        self.searchForm.showNumberDropdown = false;
                        self.searchForm.showNameDropdown = false;
                    }
                );
            });

            // Get Focus on the Input Field
            $('.modal ').on('shown.bs.modal', function (e) {
                $('#endtimeCtrl').focus();
            });

            // Open modal
            $('.modal').modal({});

            // Clear Input Field
            $("#inputClear").click(function () {
                modal.endTime = '';
            });
        },

        /**
         * Scroll to number
         * @param event
         */
        scrollToNumber: function (event) {
            var self = this;
            var input = event.target;
            if ($(input).val() > 1) {
                var tr = $("tr[data-number='" + $(input).val() + "']");
                if ($(tr).length) {
                    $('html, body').animate({
                            scrollTop: $(tr).offset().top - 40
                        }, 400, function () {
                            // On animate end
                        }
                    );

                    //Open modal
                    var index = $(tr).data('index');
                    self.openModal(index);
                }
            }
        },

        /**
         * Save data to server
         * @param index
         */
        checkOnlineStatus: function () {
            var self = this;
            var xhr = $.ajax({
                url: window.location.href,
                type: 'post',
                dataType: 'json',
                data: {
                    'action': 'checkOnlineStatus',
                    'REQUEST_TOKEN': self.requestToken,
                }
            });
            xhr.done(function (response) {
                if (response.status === 'success') {
                    self.isOnline = true;
                } else {
                    self.isOnline = false;
                }
            });
            xhr.fail(function () {
                self.isOnline = false;
            });
            xhr.always(function () {
                //
            });

        },

        /**
         * Save data to server
         * @param index
         */
        saveRow: function (index) {
            var self = this;
            var runner = self.runners[index];
            var modal = self.modal;


            var id = modal.runnerId;
            var endtime = $('#endtimeCtrl').val();
            var dnf = $('.modal #runnerdnfCtrl').is(':checked') ? 1 : '';

            // Check for a valid input f.ex. 22:12:59
            var regex = /^(([0|1][0-9])|([2][0-3])):([0-5][0-9]):([0-5][0-9])$/;

            if (regex.test(endtime) || endtime == '') {

                // Close modal and fîre xhr
                $('.modal').modal('hide');

                // Xhr
                runner.requesting = true;
                var xhr = $.ajax({
                    url: window.location.href,
                    type: 'post',
                    dataType: 'json',
                    data: {
                        'action': 'saveRow',
                        'REQUEST_TOKEN': self.requestToken,
                        'id': id,
                        'index': index,
                        'endtime': endtime,
                        'dnf': dnf
                    }
                });
                xhr.done(function (response) {

                    if (response.status == 'success') {
                        runner.requesting = false;
                        self.runners = response.data;
                        self.stats = response.stats;
                        self.categories = response.categories;
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
         * Print certificate
         * @param runnerId
         */
        printCertificate: function (runnerId) {
            window.location.href = window.location.href + '?printCertificate=true&id=' + runnerId;
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
        setEndTimeFromCurrentTime: function () {
            var self = this;
            var modal = self.modal;
            var d = new Date();
            var formatedTime = self.getFormatedTime(d);
            modal.endTime = formatedTime;
        },

        /**
         * Clear end time
         * @param index
         */
        clearEndTime: function (index) {
            var self = this;
            //var runner = self.runners[index];
            var modal = self.modal;
            modal.endTime = '';
        },

        /**
         * Show number dropdown
         * @param event
         */
        showNumberDropdownSuggest: function (event) {
            var self = this;
            var dropdown = $('#searchNumberDropdown');
            var input = event.target;

            if ($(input).val() == '') {
                self.searchForm.numberSuggests = [];
                self.searchForm.showNumberDropdown = false;
                return;
            }
            // Clean name input
            $('#searchName').val('');
            self.searchForm.nameSuggests = [];
            self.searchForm.showNameDropdown = false;

            var rows = $('#startlistTable tbody tr');

            var regex = new RegExp('^' + $(input).val() + '(.*)', 'i');

            self.searchForm.numberSuggests = [];
            rows.each(function () {
                if (regex.test($(this).attr('data-number'))) {

                    var runner = {
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
            var self = this;
            var input = event.target;
            window.setTimeout(function () {
                $(input).val('');
                self.searchForm.numberSuggests = [];
                self.searchForm.showNumberDropdown = false;
            }, 50);
        },

        /**
         * Show number dropdown
         * @param event
         */
        showNameDropdownSuggest: function (event) {
            var self = this;
            var dropdown = $('#searchNameDropdown');
            var input = event.target;

            if ($(input).val() == '') {
                self.searchForm.nameSuggests = [];
                self.searchForm.showNameDropdown = false;
                return;
            }
            // Clean number input
            $('#searchNumber').val('');
            self.searchForm.numberSuggests = [];
            self.searchForm.showNumberDropdown = false;

            var rows = $('#startlistTable tbody tr');

            var regex = new RegExp($(input).val() + '(.*)', 'i');

            self.searchForm.nameSuggests = [];

            rows.each(function () {
                if (regex.test($(this).attr('data-fullname'))) {

                    var runner = {
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
            var self = this;
            var input = event.target;
            window.setTimeout(function () {
                $(input).val('');
                self.searchForm.nameSuggests = [];
                self.searchForm.showNameDropdown = false;
            }, 50);
        },

        /**
         * Toggle sidebar
         */
        toggleSidebar: function () {
            var self = this;
            if (self.sidebar.status === 'closed') {
                self.sidebar.status = 'open';
            } else {
                self.sidebar.status = 'closed';
            }

            $('#sidebarContainer').toggleClass('hidden-sidebar');
        },

        /**
         * Apply filter
         * @param event
         */
        applyFilter: function (event) {
            var select = event.target;
            // Filter option
            var $filterCat = $(select).val();
            var rows = $('.startlist-table tbody tr');
            rows.removeClass('d-none');

            if ($filterCat == 0) return;

            rows.each(function () {
                if ($(this).attr('data-category') != $filterCat) {
                    $(this).addClass('d-none');
                }
            });
        },

        /**
         * Get formated time
         * @param d
         * @returns {string}
         */
        getFormatedTime: function (d) {
            var hours = d.getHours() < 10 ? '0' + d.getHours() : d.getHours();
            var minutes = d.getMinutes() < 10 ? '0' + d.getMinutes() : d.getMinutes();
            var seconds = d.getSeconds() < 10 ? '0' + d.getSeconds() : d.getSeconds();
            return hours + ":" + minutes + ":" + seconds;
        }
    }
});
