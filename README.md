# Orders

An orders dashboard for a fictional wholesale distributor, built with Blade
components and hand-written CSS. No Laravel, no Tailwind, no build step, and no
JavaScript.

It exists to show how I structure templates and how I handle styling, so both
are meant to be read, not just run. The two files worth opening first are
[`public/index.php`](public/index.php), which wires Blade up by hand, and
[`public/css/tokens.css`](public/css/tokens.css), which is where every design
decision in the page is actually made.

## Run it

```
composer install
composer serve      # php -S localhost:8000 -t public
composer test
```

Requires PHP 8.3 or newer.

Things worth clicking:

- `http://localhost:8000/` — the dashboard
- `http://localhost:8000/?status=cancelled&q=kepler` — the empty state (Kepler
  Dynamics AB is fulfilled, so the combination genuinely matches nothing)
- The **Auto / Light / Dark** and **Cozy / Compact** switches in the top bar
- Any column header, any status chip, any page number

All of it works with JavaScript disabled, because none of it uses JavaScript.

The built-in PHP server has no fallback router, so any URL that is neither `/`
nor a real file under `public/` returns 404. That is a property of `php -S`, not
a routing layer I forgot to write — this is one page.

## Decisions

**Blade without Laravel.** `public/index.php` does by hand what Laravel's
`ViewServiceProvider` does for you: container, filesystem, compiler, engine
resolver, factory, and the three names the factory has to be bound under. I did
this rather than pull in a wrapper package because the wiring is the most
interesting non-template file here, and because every wrapper I looked at omits
the one binding that makes `<x-...>` syntax work standalone. The tag compiler
calls `Container::getInstance()->make(Application::class)->getNamespace()` to
guess a class name before it ever looks for an anonymous template, so in a bare
container the first component tag on the page throws. All it wants is a root
namespace string. That is step 4, and it is commented in place.

The cost: framework-coupled helpers are not available and are not used —
`route()`, `asset()`, `old()`, `config()`, `@csrf`, `@error`, `@auth`. Links are
built from the GET state by three small functions in `app/Support/helpers.php`.
That is a boundary I chose, not a gap I left.

**One class-backed component, on purpose.** Every component here is an anonymous
`.blade.php` file except [`Badge`](app/View/Components/Badge.php). Anonymous
components arrange what they are handed; `Badge` *derives* — one status becomes
a variant, a label, a glyph and a spoken gloss. Put that in a template and you
get a `match` expression in the view layer, repeated wherever else needs one of
the four. Put it in a class and `table/row.blade.php` reads
`<x-badge :status="$order['status']" />`, `badge.blade.php` has no conditional
in it at all, and [`tests/badge_test.php`](tests/badge_test.php) can hold the
mapping still. The constructor takes one parameter and injects nothing, which
keeps `Component::resolve()` on its reflection fast path and the container off
the render path entirely.

**Status is never carried by colour alone.** Four glyphs from one shape family
at four fill densities, four chip border styles, and only then a hue. It
survives colour-blindness, greyscale printing and Windows High Contrast — there
is a `@media (forced-colors: active)` block for the last one, because this page
carries its whole hierarchy in hairlines and box-shadows, and forced colours
discard both.

**Fulfilled has no colour and no rail.** It is 29 of 44 rows. Marking the
majority state spends the reader's attention on the thing that needs none, so
only exceptions get the 2px left rail — a healthy queue has a visibly blank left
edge. The cost, stated plainly: operators scan for the good state too, and a
green tick is the fastest possible signal for it. I think the trade is right for
a queue you work top-down. I would want to watch someone use it before being
sure.

**The accent appears in exactly four places**, all of them chrome: the current
nav item, the current page number, the focus ring, and the active sort caret. It
never enters the table body, so green can never be misread as "this row is
good". It is a ledger green rather than the default blue partly to free blue for
Refunded, and partly so the page does not resolve to "another blue dashboard" in
the two seconds where that decision gets made.

**The dark token block is written twice.** `light-dark()` would collapse it, but
`light-dark()` inside a custom property does not degrade — it goes invalid at
computed-value time and takes the property with it, so an unsupporting browser
gets no colour rather than the light one. The duplication is ~20 aliases twice,
not 20 hex codes kept in sync by hand, because primitives are declared once and
are theme-independent. Some readers will still dislike it. I think being wrong
about support costs more than the repetition does.

**The table never becomes cards.** Columns drop in priority order — Items at
1020px, Placed at 820px — and below 680px the remaining four scroll sideways
inside a keyboard-focusable region. Restyling `<tr>` into a card drops implicit
table semantics in several screen readers, and the column relationships are the
entire value of a ledger view.

**The footer total is labelled "net of cancelled"** because it sums the same
definition the Revenue card uses. With no filter applied the two agree exactly,
which you can check by summing the Total column. Filter to Cancelled and it
correctly reads three orders, net $0.00. An unlabelled total under a table is
read as a page total, and a page total is a number nobody has a use for.

**No JavaScript.** The version of this I nearly shipped had ten lines that
removed the Apply button and auto-submitted on a pause in typing. Debounced
auto-submit moves focus mid-keystroke, which makes the search worse. Everything
here is links and a GET form, which is what it should have been anyway.

### What is not real

- **Conversion is a seeded constant.** It cannot be derived from orders without
  session data, and inventing a sessions fixture to make one card computable is
  scope this does not need. Revenue, Orders and Average order value are all
  computed from the 44 fixture rows at request time — change the fixture and
  they move.
- **The date range in the top bar is text, not a control.** A date picker that
  filtered nothing would be the only lie on the page.
- **There is no order detail view**, so an order number links to that order
  filtered rather than to a route that would 404. Every link here goes somewhere
  real.
- **`composer install` pulls roughly twenty packages.** `illuminate/view` has
  real transitive dependencies — `symfony/finder`, `doctrine/inflector`,
  `nesbot/carbon` among them. This is not a "zero dependency" project and I am
  not going to describe it as one.

## How this was checked

Each of these is a thing done, not a compliance claim — and each takes under a
minute to repeat.

- `composer test` — 17 assertions over the status mapping and the three metric
  definitions, including that the README's empty-state URL genuinely returns
  nothing.
- Tabbed the page from the address bar: skip link first, visible focus ring on
  every control, nothing focusable that is not a control.
- Loaded it with JavaScript disabled and used every filter, sort, page, theme
  and density control.
- Switched OS appearance with the theme on Auto, then overrode each way, and
  checked that the scrollbar and text caret follow the theme rather than staying
  light at the edges.
- Resized continuously from 1600px to 320px and watched each column drop at its
  stated breakpoint.

Still unverified, and I would rather say so than imply otherwise: Windows High
Contrast (the `forced-colors` block is written but I have no Windows machine
here), and a real screen-reader pass beyond checking the markup the table
announces from.

## Fit

This is a deliberately small surface, so it shows some things and not others.
It shows how I decompose a page into components and where I think logic belongs;
it shows that I would rather write 60 lines of CSS with a reason than install
something. It does not show anything about data access, testing at scale,
queues, or how I work in someone else's codebase — which is usually the more
useful conversation.

If it is helpful, tell me what your stack actually looks like and I will say
plainly where this maps and where it does not.
