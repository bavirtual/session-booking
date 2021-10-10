# Change Log

## [0.02.73] BETA - RELEASED 2021-10-10 - hotfix

### Added
- subscriber_vault_interface
- subscriber_vault

### Changed
- version 2021101000
- subscriber data access SQL to vaults

### Fixed
- context id not being passed correctly to js after last hotfix

## [5.0.6] RELEASED 01/06/2021

### Added
- validationEventTimeOut option

### Changed
- User Webpack/Terserplugin for minification
- Fix caret shift with negative numbers in numeric aliases
- enhance alternation logic
- update datetime alias
- datetime prefillYear option
    Enable/disable prefilling of the year.
    Although you can just over type the proposed value without deleting, many seems to see a problem with the year prediction.
    This options is to disable this feature.
- better handle maxLength

### Fixed
- Decimal mask with maxlength turns integer into real number on maximum length #2260
- jitMasking removing a decimal after the comma #2494
- Issue with negative values and prefix in currency mask #2476
- persian/arabic currency mask with regex #2472
- Issue with negative values and prefix in currency mask #2476
- Selecting all + backspace goes to the end of the input #2336
- Error thrown, if only insert radixpoint and leave field and placeholder = "" #2475
- Datetime alias with day auto-fill problem #2480
- Suppress DateTime year autocomplete? #2395
- Bug in iframes #2461
- stuck with cursor on / text of date with datetime extension #2464
- Inputmask with a _space_ as a placeholder and leap year date #2451
- setvalue() "removes" number before comma when "positionCaretOnClick" and "digitsOptional" are set. #2457
- Date field results into buggy output: 30/02/yy0y #2456
- cant enter the leap year using jit masking #2453
- Basically the same issue appears also when you have a valid date in the input but want to change something. #2435
- Can't remove "placeholder" from datetime alias #2438
- showMaskOnFocus: false causes 'Illegal invocation' error #2436
- Input Mask for search fields (partially filled mask) #2425
- HandleNativePlaceholder function prevents use of dynamic placeholders. #2433
- '0' getting added unnecessarily if navigating using arrow key for datetime input mask #2289
- jitmasking ssn #2420
- Removing the mask from Input results in TypeError: Cannot read property 'dependencyLib' of undefined #2403
- Country Code Problem #2397
- Error thrown in unmask after upgrade to 5.0.5 #2375
- Inputmask.remove(document.getElementById(selector)) is not working in Node after version 5.0.5 update #2373
- date format yyyy-mm-dd doesn't work with min and max #2360
- Datetime inputFormat mm/dd/yyyy allows entry of 02/3 without padding the day #1922

## [3.3.3 - 2016-09-09] - hotfix

### Changed
- revert moving jquery dependencyLib
- correct caret positioning - radixFocus & placeholder: ""

### Fixed
- Build failure in heroku after release of 3.3.2 #1384
- Error with inputMask any case (v3.3.2) #1383

## [3.1.62] - 2015-03-26
### Added
- Numeric alias: add unmaskAsNumber option
- import russian phone codes from inputmask-multi
- enable masking the text content in a div
- enable contenteditable elements for inputmask
- Update Command object to handle inserts and allow for multiple removes
- Add a change log
- Add Component package manager support - component.json

### Fixed
- updating a value on onincomplete event doesn't work #955
- $.inputmask.isValid("1A", { mask : "1A" }) returns false #858
- IE8 doesn't support window.getSelection js error #853
- Email with dot - paste not working #847
- Standard phone numbers in Brazil #836 (Part 1)
- Sequentional optional parts do not fully match #699
- How i fix that number problem? #835
- Form reset doesn't get same value as initial mask #842
- Numeric extension doesn't seem to support min/max values #830
- Numeric max filter #837
- Mask cache - 2 definitions for same mask #831
- Adding parentheses as a negative format for Decimal and Integer aliases (100) #451
- Should not allow "-" or "+" as numbers #815
- isComplete erroneously returning false when backspacing with an optional mask #824

## [0.02.43 UNRELEASED] - ALPHA
### Added
- Logbook entry functionality

### Fixed
- Availability timeslot posting not marking properly

## [0.02.43 UNRELEASED] - ALPHA
### Added
- Logbook entry functionality

### Fixed
- Availability timeslot posting not marking properly

Initial start of a changelog

See commits for previous history.
