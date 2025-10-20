# Contributing to Paygate

Thank you for considering contributing to Paygate! This document provides guidelines and information for contributors.

## Table of Contents

- [Code of Conduct](#code-of-conduct)
- [Getting Started](#getting-started)
- [Development Setup](#development-setup)
- [Contributing Guidelines](#contributing-guidelines)
- [Pull Request Process](#pull-request-process)
- [Issue Reporting](#issue-reporting)
- [Feature Requests](#feature-requests)

## Code of Conduct

This project and everyone participating in it is governed by our Code of Conduct. By participating, you are expected to uphold this code.

## Getting Started

1. **Fork the repository** on GitHub
2. **Clone your fork** locally
3. **Create a new branch** for your feature or bugfix
4. **Make your changes** following our guidelines
5. **Test your changes** thoroughly
6. **Submit a pull request**

## Development Setup

### Prerequisites

- PHP 8.1 or higher
- Composer
- Laravel 9.0 or higher
- MySQL/PostgreSQL
- Node.js (for frontend assets)

### Installation

```bash
# Clone your fork
git clone https://github.com/your-username/paygate.git
cd paygate

# Install dependencies
composer install

# Install development dependencies
composer install --dev

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Run migrations
php artisan migrate

# Run tests
php artisan test
```

### Testing

```bash
# Run all tests
php artisan test

# Run specific test suites
php artisan test --filter=PaymentTest
php artisan test --filter=PaymentServiceTest

# Run with coverage
php artisan test --coverage

# Run tests in parallel
php artisan test --parallel
```

## Contributing Guidelines

### Code Style

- Follow PSR-12 coding standards
- Use meaningful variable and method names
- Add docblocks for all public methods
- Keep methods focused and single-purpose
- Use type hints where appropriate

### Git Workflow

1. **Create a feature branch** from `main`
   ```bash
   git checkout -b feature/your-feature-name
   ```

2. **Make your changes** and commit them
   ```bash
   git add .
   git commit -m "Add: your feature description"
   ```

3. **Push your branch** to your fork
   ```bash
   git push origin feature/your-feature-name
   ```

4. **Create a pull request** on GitHub

### Commit Message Format

Use the following format for commit messages:

```
type(scope): description

[optional body]

[optional footer]
```

**Types:**
- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation changes
- `style`: Code style changes
- `refactor`: Code refactoring
- `test`: Test additions or changes
- `chore`: Build process or auxiliary tool changes

**Examples:**
```
feat(payment): add refund functionality
fix(webhook): resolve signature verification issue
docs(readme): update installation instructions
test(payment): add unit tests for payment service
```

### Code Quality

- **Write tests** for new features
- **Update documentation** for API changes
- **Follow existing patterns** in the codebase
- **Keep functions small** and focused
- **Use meaningful names** for variables and methods
- **Add comments** for complex logic

## Pull Request Process

### Before Submitting

1. **Ensure all tests pass**
2. **Update documentation** if needed
3. **Add tests** for new functionality
4. **Check code style** with PHP CS Fixer
5. **Update CHANGELOG.md** with your changes

### Pull Request Template

```markdown
## Description
Brief description of changes

## Type of Change
- [ ] Bug fix
- [ ] New feature
- [ ] Breaking change
- [ ] Documentation update

## Testing
- [ ] Tests pass locally
- [ ] New tests added for new functionality
- [ ] Manual testing completed

## Checklist
- [ ] Code follows project style guidelines
- [ ] Self-review completed
- [ ] Documentation updated
- [ ] CHANGELOG.md updated
```

### Review Process

1. **Automated checks** must pass
2. **Code review** by maintainers
3. **Testing** in staging environment
4. **Approval** from at least one maintainer

## Issue Reporting

### Bug Reports

When reporting bugs, please include:

- **Laravel version**
- **PHP version**
- **Package version**
- **Steps to reproduce**
- **Expected behavior**
- **Actual behavior**
- **Error messages** (if any)
- **Screenshots** (if applicable)

### Feature Requests

When requesting features, please include:

- **Use case description**
- **Proposed solution**
- **Alternative solutions considered**
- **Additional context**

## Development Guidelines

### Adding New Payment Gateways

1. **Create gateway service** in `src/Services/Gateways/`
2. **Implement PaymentGatewayInterface**
3. **Add configuration** in `config/paygate.php`
4. **Add tests** for the new gateway
5. **Update documentation**

### Adding New Features

1. **Create feature branch**
2. **Implement feature** following existing patterns
3. **Add comprehensive tests**
4. **Update documentation**
5. **Submit pull request**

### Database Changes

1. **Create migration** for schema changes
2. **Update model** if needed
3. **Add tests** for new functionality
4. **Update documentation**

## Testing Guidelines

### Unit Tests

- Test individual methods and classes
- Mock external dependencies
- Test edge cases and error conditions
- Aim for high code coverage

### Feature Tests

- Test complete user workflows
- Test API endpoints
- Test database interactions
- Test error handling

### Integration Tests

- Test with real payment gateways (sandbox)
- Test webhook handling
- Test event dispatching
- Test middleware functionality

## Documentation

### Code Documentation

- Add docblocks for all public methods
- Include parameter and return type information
- Provide usage examples where helpful
- Keep documentation up to date

### User Documentation

- Update README.md for new features
- Add examples and use cases
- Update installation instructions
- Add troubleshooting guides

## Release Process

### Version Numbering

We follow [Semantic Versioning](https://semver.org/):

- **MAJOR**: Breaking changes
- **MINOR**: New features (backward compatible)
- **PATCH**: Bug fixes (backward compatible)

### Release Checklist

1. **Update version numbers**
2. **Update CHANGELOG.md**
3. **Run full test suite**
4. **Update documentation**
5. **Create release tag**
6. **Publish to Packagist**

## Community

### Getting Help

- **GitHub Discussions**: For questions and general discussion
- **GitHub Issues**: For bug reports and feature requests
- **Discord**: For real-time chat and support

### Recognition

Contributors will be recognized in:
- **README.md** contributors section
- **CHANGELOG.md** for significant contributions
- **Release notes** for major contributions

## License

By contributing to Paygate, you agree that your contributions will be licensed under the same MIT License that covers the project.

---

Thank you for contributing to Paygate! 🚀
