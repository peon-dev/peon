## Unit tests

TODO: improve later

- Mocks are widely used
- Must not communicate with outer world
- Must not use DI container
- Must not use any other service, needs bare PHP to run

## Integration tests

TODO: improve later

- Can use mocks
- Uses database
- Data are taken
- Tests read model

## Application tests

Tests simulating HTTP requests. Test classes usually extends `Symfony\Bundle\FrameworkBundle\Test\WebTestCase`.

- These tests use data from fixtures.
- To test specific scenarios (edge cases), data can be prepared as part of the test case
- No mocking allowed
- Must not communicate with outer world
- Needs frontend assets to be built to run

Goal of application tests is to test:
- Page can be rendered
- Page contains expected elements
- Form can be sent
- <small>Maybe check that data were passed successfully from application layer to domain? Not sure about this one, if it is needed to test :-)</small>

## End to end tests

Test scenarios must run against fully-working application including all components (with `APP_ENV=prod` in CI).

Tests itself does not have to be written in PHP (but ofc can be).

Should not be part of default PHPUnit testsuite - because can not be run by any contributor, only by maintainers.

- Can use fixtures or can prepare all data itself by user scenarios
- Should communicate with outer world
- Needs secrets

Goal is to make sure user can proceed with use cases at any given time.
