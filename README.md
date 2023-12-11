# JsonEncoder examples

A demo project showing the main differences between the usage of the
Serializer component and the JsonEncoder component.

Relevant examples can be found in: `src/Command`.

## Sum up of differences

While the Serializer component is focused on flexibility, and covers a huge
amount of use cases, the JsonEncoder component is more focused on efficiency.

Indeed, by design, the Serializer serializes data using two steps:
normalization/denormalization and encoding/decoding. This implies
that the whole normalized/decoded data is at some point in memory.

On the other hand, the JsonEncoder normalizes/denormalizes and
encodes/decodes data at the same time, which allows to leverage streams,
performance and laziness.

For the sake of performance, the JsonEncoder differs from the Serializer in
some other points:
- during cache warmup, the data shape is precompiled, and some PHP code is
  generated. Therefore there is no way to adapt the data shape depending on
  the data itself.
- because there is no normalization step, there is no point in using
  normalizers/denormalizers. Static formatters must be used instead.
- the JsonEncoder only deals with public properties (but this can be
  challenged, and there is no blocker to leverage the property accessor
  instead).

## When to use the Serializer component or the JsonEncoder component? 

Most of the time, developers will use the Serializer component, because most of
the time, the performance provided by that component will fit their needs. So
they will seamlessly benefit from the flexibility.

But, when it comes to big amounts of data, or a need for high speed, developers
will be able to leverage the JsonEncoder component directly.

When can see the difference between the Serializer component and the JsonEncoder
component like the difference between Doctrine ORM and Doctrine DBAL.

Indeed, the DBAL can be considered as a sub-layer of ORM, and when precise and
performance-related stuff is needed, developers will skip the ORM layer to deal
with the DBAL one directly.

And it's the very same difference between the Serializer and the JsonEncoder,
when precise and performance-related stuff is needed, developers will skip the
normalization layer, by fine-tuning the data mapping in their userland and deal
with the encoding layer directly.

Another way to consider the JsonEncoder is as the `json_encode`/`json_decode`
functions, but on steroids.

Therefore, the JsonEncoder can and must at some point replace the existing JSON
encoder of the Serializer, so that only one JSON encoder exists in the Symfony
ecosystem.

Plus, the availability to handle streams is making the JsonEncoder a perfect
candidate to deal with uses cases with huge amout of data, such as ETL or
datalakes. Indeed, in theses cases, it is almost impossible to escape the OOM
issues without streaming.

In addition to developers facing the challenges mentioned above in final
applications, Symfony itself already builds on JSON streaming through e.g. the
recently introduced `StreamedJsonResponse` and will do more and more in the
coming years notably through API Platform. As such, I think Symfony would better
be the one providing the foundations for this and should rather gain adoption
instead of having its users forced to look at other, external alternatives such
as https://github.com/halaxa/json-machine or https://github.com/cerbero90/json-parser.
