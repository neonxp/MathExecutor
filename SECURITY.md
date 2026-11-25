# Security Policy

## Overview

This security policy outlines how we handle security concerns and maintain the integrity of our code.

## Repository Type

**This is a PHP Packagist compatible repository.** It contains the following components:
- src directory containing all source files
- tests directory containing all PHPUnit tests
- .github directory containing all github workflows
- .githooks directory containing local githooks

## Supported Versions

| Status | Description |
|--------|-------------|
| Latest | Current version, actively maintained |
| Outdated | Previous versions; consider updating |

## Reporting a Security Vulnerability

### Security Issues to Report

- **Malicious links** in any source or markdown files
- **Incorrect security advice** that could harm users
- **Sensitive information** accidentally included
- **Copyright violations** or licensing issues
- **Offensive or harmful content**

### How to Report

**DO NOT** create a public GitHub issue for sensitive matters.

Instead, contact: **[Bruce Wells](mailto:brucekwells@gmail.com)**

Or open a **private security advisory** on GitHub:
1. Go to **Security** tab ? **Advisories**
2. Click **Report a vulnerability**
3. Provide details of the issue

### What to Include

- **Type of issue** (malicious link, potential harmful code, offensive content, etc.)
- **Location** (file and line number(s))
- **Description** of the problem
- **Suggested fix** or correction
- **Impact** (how this affects users)

### Response Timeline

- **Initial Response:** Within 24-48 hours
- **Investigation:** Within 1 week
- **Resolution:** Patch released within 1-2 weeks
- **Public Disclosure:** After fix is merged

## Content Security Guidelines

### For Contributors

- **Verify information:** Ensure all technical content is accurate and tested
- **No malicious code:** Never include code designed to harm or exploit
- **Protect privacy:** Don't include API keys, credentials, or personal data
- **Respect licenses:** Credit and link to original sources
- **Ethical guidance only:** No hacking or illegal techniques without legal context
- **No external scripts:** Avoid embedding external JavaScript or tracking code
- **Link validity:** Verify all links work and lead to legitimate resources

### Linking Best Practices

- + Link to official documentation
- + Link to reputable technical resources
- + Link to GitHub repositories with clear licenses
- + Use HTTPS URLs whenever possible
- - Don't link to pirated content
- - Don't link to malware or exploits
- - Don't link to personal tracking services

### Code Examples

- **Tested code:** Verify all code examples work correctly
- **Security first:** Show secure practices, not vulnerable code
- **Clear disclaimers:** Warn if example is for educational purposes only
- **No credentials:** Never include real API keys or passwords in examples
- **Dependencies:** List all required dependencies clearly

## Organization-Wide Security

This repository follows these security practices:

- **Automated scans:** GitHub's built-in security features monitor for vulnerabilities
- **Branch protection:** All changes reviewed before merging
- **Public source:** All content is open-source and transparent
- **MIT Licensed:** Free for use, modification, and distribution

## Handling Security Issues in Content

### Outdated Security Advice

If you find security advice that's outdated or dangerous:
1. Report it immediately (see above)
2. We will add a deprecation notice
3. We will publish the corrected version
4. We will credit the reporter (if desired)

### Malicious or Offensive Content

We have zero tolerance for:
- Hacking for illegal purposes
- Offensive or discriminatory language
- Spam or promotional content
- Misinformation about security practices

Such content will be:
1. Immediately removed
2. Reported to GitHub if necessary
3. Contributor banned from contributing

## User Safety

When using cheat sheets from this repository:

- Test code in safe environments first
- Read all warnings and disclaimers
- Verify information from multiple sources
- Report unsafe or incorrect content
- Use at your own risk
- Not liable for data loss or damage

## Third-Party Tools Referenced

This repository may reference external tools, services, or libraries:

- Always review their official security documentation
- Check for known vulnerabilities before use
- Keep external tools updated
- Verify tool authenticity

## Disclaimer

This package is provided **as-is**. While we strive for accuracy:

- Users are responsible for verifying information
- Test code in safe environments first
- We provide no warranty of correctness
- We are not liable for damages from using this content

## Security Checklist

Before publishing, ensure:

- [ ] No credentials or secrets included
- [ ] All links are valid and legitimate
- [ ] Code examples are tested and safe
- [ ] Security advice is current and accurate
- [ ] No offensive or harmful content
- [ ] License and attribution properly credited
- [ ] Links use HTTPS
- [ ] No tracking or analytics code
- [ ] Reviewed by at least one maintainer

## Questions & Concerns

For security questions, contact:

- **Email:** **[Bruce Wells](mailto:brucekwells@gmail.com)**

---

**Last Updated:** 2026-11-25
**License:** MIT

