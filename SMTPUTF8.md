# A short history of UTF-8 in email

## Background

For most of its existence, SMTP has been a 7-bit channel, only supporting US-ASCII characters. This has been a problem for many languages, especially those that use non-Latin scripts, and has led to the development of various workarounds.

The first major improvement, introduced in 1994 in [RFC 1652](https://www.rfc-editor.org/rfc/rfc1652) and extended in 2011 in [RFC 6152](https://www.rfc-editor.org/rfc/rfc6152), was the addition of the `8BITMIME` SMTP extension, which allowed raw 8-bit data to be included in message bodies sent over SMTP.
This allowed the message *contents* to contain 8-bit data, including things like UTF-8 text, even though the SMTP protocol itself was still firmly 7-bit. This worked by having the server switch to 8-bit after the headers, and then back to 7-bit after the completion of a `DATA` command.

From 1996, messages could support [RFC 2047 encoding](https://www.rfc-editor.org/rfc/rfc2047), which permitted inserting characters from any character set into header *values* (but not names), but only by encoding them in somewhat unreadable ways to allow them to survive passage through a 7-bit channel. An example with a subject of "Schrödinger's cat" would be:

```
Subject: =?utf-8?Q=Schr=C3=B6dinger=92s_Cat?=
```

Here the accented `ö` is encoded as `=C3=B6`, which is the UTF-8 encoding of the 2-byte character, and the whole thing is wrapped in `=?utf-8?Q?` to indicate that it uses the UTF-8 charset and `quoted-printable` encoding. This is a bit of a hack, and not very human-friendly, but it works.

Similarly, 8-bit message bodies could be encoded using the same `quoted-printable` and `base64` content transfer encoding (CTE) schemes, which preserved the 8-bit content while encoding it in a format that could survive transmission through a 7-bit channel.

Domain names were originally also stuck in a 7-bit world, actually even more constrained to only a subset of the US-ASCII character set. But of course, many people want to have domains in their own language/script. Internationalized domain name (IDN) permitted this, using yet another complex encoding scheme called punycode, defined for domain names in 2003 in [RFC 3492](https://www.rfc-editor.org/rfc/rfc3492). This finally allowed the domain part (after the `@`) of email addresses to contain UTF-8, though it was actually an illusion preserved by email client applications. For example, an address of
`user@café.example.com` translates to
`user@xn--caf-dma.example.com` in punycode, rendering it mostly unreadable, but 7-bit friendly, and remaining compatible with email clients that don't know about IDN.

The one remaining part of email that could not handle UTF-8 is the local part of email addresses (the part before the `@`).

I've only mentioned UTF-8 here, but most of these approaches also allowed other character sets that were popular, such as [the ISO-8859 family](https://en.wikipedia.org/wiki/ISO/IEC_8859). However, UTF-8 solves so many problems that these other character sets are gradually falling out of favour, as UTF-8 can support all languages.

This patchwork of overlapping approaches has served us well, but we have to admit that it's a mess.

## SMTPUTF8

`SMTPUTF8` is another SMTP extension, defined in [RFC 6531](https://www.rfc-editor.org/rfc/rfc6531) in 2012. This essentially solves the whole problem, allowing the entire SMTP conversation — commands, headers, and message bodies — to be sent in raw, unencoded UTF-8.

But there's a problem with this approach: adoption. If you send a UTF-8 message to a recipient whose mail server doesn't support this format, the sender has to somehow downgrade the message to make it survive a transition to 7-bit. This is a hard problem to solve, especially since there is no way to make a 7-bit system support UTF-8 in the local parts of addresses. This downgrade problem is what held up the adoption of `SMTPUTF8` in PHPMailer for many years, but in that time the *de facto* approach has become to simply fail in that situation, and tell the recipient it's time they upgraded their mail server 😅.

The vast majority of large email providers (gmail, Yahoo, Microsoft, etc), mail servers (postfix, exim, IIS, etc), and mail clients (Apple Mail, Outlook, Thunderbird, etc) now all support SMTPUTF8, so the need for backward compatibility is no longer what it was.

## SMTPUTF8 in PHPMailer

Several other PHP email libraries have implemented a halfway solution to `SMTPUTF8`, adding only the ability to support UTF-8 in email addresses, not elsewhere in the protocol. I wanted PHPMailer to do it "the right way", and this has taken much longer. PHPMailer now supports UTF-8 everywhere, and does not need to use transfer or header encodings for UTF-8 text when connecting to an `SMTPUTF8`-capable mail server.

This support is handled automatically: if you add an email address that requires UTF-8, PHPMailer will use UTF-8 for everything. If not, it will fall back to 7-bit and encode the message as necessary.

The one place you will need to be careful is in the selection of the address validator. By default, PHPMailer uses PHP's built-in `filter_var` validator, which does not allow UTF-8 email addresses. When PHPMailer spots that you have submitted a UTF-8 address, but have not altered the default validator, it will automatically switch to using a UTF-8-compatible validator. As soon as you do this, any SMTP connection you make will *require* that the server you connect to supports `SMTPUTF8`. You can select this validator explicitly by setting `PHPMailer::$validator = 'eai'` (an acronym for Email Address Internationalization).

### Postfix gotcha

Postfix has supported `SMTPUTF8` for a long time, but it has a peculiarity that it does not always advertise that it does so. However, rather surprisingly, if you use UTF-8 in the conversation, it will work anyway.
