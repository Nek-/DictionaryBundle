# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [2.0.0] - 2017-12-12
### Added
- Add tag `knp_dictionary.factory` for DI services so you can add dictionary factories
- Add tag `knp_dictionary.dictionary` for DI services so you can add dictionary as service
- Add `knp_dictionary.data_collector.dictionary_data_collector` service so you can trace what dictionnary is available
- New factory interface `Knp\DictionaryBundle\Dictionary\Factory`
- New interface `Knp\DictionaryBundle\Dictionary`
- This changelog is also new :)

### Changed
- Some namespace changes for `SimpleDictionary` and `ValueTransformer`
- Add `add` method on `DictionaryRegistry`
- New view in the web profiler of Symfony

### Removed
- `service` and `method` nodes from configuration: dictionnaries are now loaded automatically by factories registered as it with the new tag
