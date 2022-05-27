![Alt text](https://github.com/markocupic/markocupic/blob/main/logo.png "logo")

# Contao Backend E-Mail
Contao E-Mail Erweiterung für den Versand von Nachrichten aus dem Contao Backend. In den Contao Einstellungen kann definiert werden, woher das Adressbuch seine Adressen zieht. Entweder tl_member oder tl_user oder aus beiden Tabellen.

![Frontend](docs/screenshot.png "frontend")

# Chronometry-bundle
Zeiterfassungs Tool für Ausdauerwettkämpfe programmiert für die Schule Ettiswil. Die App basiert auf dem Javascript Framework [vue.js](https://vuejs.org/) und kann lokal auf xampp ausgeführt werden. Alle Ressourcen* sind lokal im public-Verzeichnis des Moduls vorhanden.

### CSV
Im Verzeichnis "docs" befindet sich eine Beispiel-CSV-Datei, welche zum Aufbau der Datenbank benutzt werden kann. Die Datei lässt sich dann mit [markocupic/import-from-csv-bundle](https://github.com/markocupic/import-from-csv-bundle) in die Datenbank (tl_chronometry) einlesen.

### Seitentemplate
Neben dem Modul-Verzeichnis gibt es in src/Resources/contao/templates/frontend/fe_page_chronometry.html5 ein Seitentemplate, welches bereits für Bootstrap vorbereitet ist.

### Diplom
Über das Modalfenster kann anlässlich einer Siegerehrung ein Diplom (MS-Word-Dokument) ausgedruckt werden.

### Abhängigkeiten
Diese Ressourcen werden im Template eingebunden und befinden sich in src/Resources/public
* vue.js
* Font Awesome 5 Free
* Bootstrap Framework
* jQuery (muss im Theme im Contao Backend eingebunden werden)
* popper.js
* [stupid-table table sorter](https://github.com/joequery/Stupid-Table-Plugin)




