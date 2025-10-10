@extends('layouts.app')
@section('title','कार्यसमिति')

@section('content')

<section class="max-w-6xl mx-auto px-4 py-10">
  <!-- Title -->
  <header class="mb-8">
    <h1 class=" text-center text-2xl sm:text-3xl font-extrabold text-blue-700">
      मेन्याङ्गबो कल्याणकारी संघ नेपाल
    </h1>
    <p class="text-center text-slate-600 mt-1">केन्द्रिय कार्यसमितिको नामावली विवरण</p>
  </header>

  <!-- Top posts -->
  <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
    <!-- संरक्षक -->
    <article class="rounded-2xl border border-slate-200 bg-white shadow-sm p-5">
      <h3 class="text-sm font-semibold text-slate-600">संरक्षक</h3>
      <p class="mt-1 text-lg font-bold">श्री पुत्रबहादुर मेन्याङ्बो</p>
    </article>

    <!-- अध्यक्ष -->
    <article class="rounded-2xl border border-slate-200 bg-white shadow-sm p-5">
      <h3 class="text-sm font-semibold text-slate-600">अध्यक्ष</h3>
      <p class="mt-1 text-lg font-bold">श्री ललित बहादुर मेन्याङ्बो</p>
    </article>

    <!-- उपाध्यक्षहरू -->
    <article class="rounded-2xl border border-slate-200 bg-white shadow-sm p-5">
      <h3 class="text-sm font-semibold text-slate-600">उपाध्यक्ष</h3>
      <ul class="mt-1 space-y-1 text-slate-800">
        <li>श्री रमेशकुमार मेन्याइयो</li>
        <li>श्री मनोजकुमार मेन्याङ्बो (विराटनगर)</li>
      </ul>
    </article>

    <!-- महासचिव / सचिव -->
    <article class="rounded-2xl border border-slate-200 bg-white shadow-sm p-5">
      <h3 class="text-sm font-semibold text-slate-600">महासचिव</h3>
      <p class="mt-1 font-semibold">श्री सुरेन्द्रबहादुर मेन्याङ्बो</p>
      <h3 class="mt-4 text-sm font-semibold text-slate-600">सचिव</h3>
      <p class="mt-1 font-semibold">श्री शुकराज मेन्याङ्बो</p>
    </article>

    <!-- कोषाध्यक्ष / सह-कोषाध्यक्ष -->
    <article class="rounded-2xl border border-slate-200 bg-white shadow-sm p-5">
      <h3 class="text-sm font-semibold text-slate-600">कोषाध्यक्ष</h3>
      <p class="mt-1 font-semibold">श्री मोतिबहादुर मेन्याङ्बो</p>
      <h3 class="mt-4 text-sm font-semibold text-slate-600">सह-कोषाध्यक्ष</h3>
      <p class="mt-1 font-semibold">श्री छत्रबहादुर मेन्याङ्बो</p>
    </article>

    <!-- कार्यालय / पत्ता -->
    <article class="rounded-2xl border border-slate-200 bg-white shadow-sm p-5">
      <h3 class="text-sm font-semibold text-slate-600">केन्द्रिय कार्यालय</h3>
      <p class="mt-1">धरान–१५, सुनसरी, नेपाल</p>
    </article>
  </div>

  <!-- Members -->
  <section class="mt-10">
    <div class="flex items-end justify-between">
      <h2 class="text-xl font-bold text-blue-700">सदस्य</h2>
    </div>

    <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
      <!-- Each list card -->
      <ul class="rounded-2xl border border-slate-200 bg-white shadow-sm p-4 text-slate-800 space-y-2">
        <li>श्रीमती मीरा मेन्याङ्गबो</li>
        <li>श्रीमती सवि मेन्याइयो</li>
        <li>श्री डम्बरबहादुर मेन्याङ्गयो (झिलझिले, झापा)</li>
        <li>श्री रणबहादुर मेन्याङ्गबो (इटहरी)</li>
        <li>श्री हिमलाल मेन्याङ्गबो (थेचम्बु, ताप्लेजुङ्ग)</li>
        <li>श्री दिपक मेन्याङ्बो (धरान)</li>
      </ul>
      <ul class="rounded-2xl border border-slate-200 bg-white shadow-sm p-4 text-slate-800 space-y-2">
        <li>श्री कुलप्रशाद मेन्याङ्गबी (कुपण्डोल, ललितपुर)</li>
        <li>श्री प्रदिप मेन्याङ्गबो (धरान)</li>
        <li>श्री आकाशहाङ मेन्याङ्बो (इटहरी)</li>
        <li>श्री बमबहादुर मेन्याङ्गबो (बेलबारी, लालनिक्ति, मोरङ्ग)</li>
        <li>श्री यामबहादुर मेन्याङ्गबो (धरान)</li>
        <li>श्री रामबहादुर मेन्याङ्गबो (धरान)</li>
      </ul>
      <ul class="rounded-2xl border border-slate-200 bg-white shadow-sm p-4 text-slate-800 space-y-2">
        <li>श्री निर्मल मेन्याङ्गबो (धरान)</li>
        <li>श्री निरप्रशाद मेन्याङ्गष</li>
        <li>श्रीमती मनिता मेन्याङ्गबो (धरान)</li>
      </ul>
    </div>
  </section>

  <!-- Past leadership -->
  <section class="mt-10">
    <h2 class="text-xl font-bold text-blue-700">भू. पू. पदाधिकारी</h2>

    <div class="mt-4 grid gap-4 md:grid-cols-2">
      <div class="rounded-2xl border border-slate-200 bg-white shadow-sm p-5">
        <h3 class="text-sm font-semibold text-slate-600">भू. पू. अध्यक्षहरू</h3>
        <ul class="mt-2 list-disc ms-5 space-y-1 text-slate-800">
          <li>श्री अकलबहादुर मेन्याङ्गबो</li>
          <li>श्री गणेशबहादुर मेन्याङ्बो</li>
          <li>श्री शान्ताहाङ मेन्याङ्गबो</li>
        </ul>
      </div>

      <div class="rounded-2xl border border-slate-200 bg-white shadow-sm p-5">
        <h3 class="text-sm font-semibold text-slate-600">भू. पू. उपाध्यक्षहरू</h3>
        <ul class="mt-2 list-disc ms-5 space-y-1 text-slate-800">
          <li>श्री ललितबहादुर मेन्याङ्बो</li>
          <li>श्री खड्कबहादुर मेन्याङ्वो</li>
          <li>श्री नैयन्द्रकुमार मेन्याङ्गयो</li>
          <li>श्री मानबहादुर मेन्याङ्बो</li>
        </ul>
      </div>
    </div>
  </section>

  <!-- Advisors -->
  <section class="mt-10">
    <h2 class="text-xl font-bold text-blue-700">विशिष्ट सल्लाहकार / कानूनी सल्लाहकार</h2>

    <div class="mt-4 grid gap-4 lg:grid-cols-2">
      <ul class="rounded-2xl border border-slate-200 bg-white shadow-sm p-5 space-y-2 text-slate-800">
        <li>रथि/अ. पा. श्री फत्तेबहादुर मेन्याइयो</li>
        <li>समाजसेवी ह. कप्तान श्री पृथीबहादुर मेन्याङ्गबो</li>
        <li>पूर्व सांसद तिलकुमार मेन्याङ्बो</li>
        <li>समाजशास्त्री डा. चैतन्य सुब्बा ल्याइयो</li>
        <li>पूर्व अम्बल प्रशासक श्री कुमार सुब्बा मेन्याङ्वो</li>
        <li>सह प्रा. श्री ध्रुवकुमार शुब्बा मेन्याइयो</li>
        <li>समाजसेवी श्री सन्तोषकुमार मेन्या (किलकिले, झापा)</li>
        <li>अधिवक्ता, उप प्रा. श्री रामप्रशाद मेन्याङ्गबो</li>
        <li>पूर्व मेयर श्री मनोजकुमार मेन्याङ्वो</li>
      </ul>

      <ul class="rounded-2xl border border-slate-200 bg-white shadow-sm p-5 space-y-2 text-slate-800">
        <li>तुम्याहाङ्ग तथा मुन्धुमविद : श्री लक्ष्मण मेन्याङ्गबो</li>
        <li>कानूनी सल्लाहकार — सहायक प्राध्यापक / अधिवक्ता श्री पूर्णबहादुर मेन्याङ्गबो</li>
      </ul>
    </div>
  </section>

  <!-- Central executive list (alternate block you provided) -->
  <section class="mt-10">
    <h2 class="text-xl font-bold text-blue-700">केन्द्रिय कार्यकारिणि समिति — नामावली</h2>

    <div class="mt-4 rounded-2xl border border-slate-200 bg-white shadow-sm p-5">
      <ol class="space-y-2 text-slate-800">
        <li>१. अध्यक्षः श्रीमान ललित बहादुर मेन्याङ्गबो — धरान, सुनसरी</li>
        <li>२. उपध्यक्षः श्रीमान रमेस कुमार मेन्याङ्गबो — धरान, सुनसरी</li>
        <li>३. उपाध्यक्षः श्रीमान मनोज कुमार मेन्याङ्ग्बो — विराटनगर, मोरङ</li>
        <li>४. महासचिवः श्रीमान सुरेन्द्र बहादुर मेन्याङ्ग्बो — धरान, सुनसरी</li>
        <li>५. कोषाध्यक्षः श्रीमान मोति बहादुर मेन्याङ्बो — धरान, सुनसरी</li>
        <li>६. सचिवः श्रीमान सुकराज मेन्याङ्बो — धरान, सुनसरी</li>
        <li>७. सह-कोषाध्यक्षः श्रीमान छत्रबहादुर मेन्याङ्ग्बो — धरान, सुनसरी</li>
      </ol>

      <h3 class="mt-5 text-sm font-semibold text-slate-600">सदस्य</h3>
      <ol class="mt-2 grid gap-1 sm:grid-cols-2">
        <li>८. श्रीमति विनिता मेन्याङ्बो — धरान, सुनसरी</li>
        <li>९. श्रीमति कल्पना मेन्याङ्बो — धरान, सुनसरी</li>
        <li>१०. श्रीमति सुनिता मेन्याङ्बो — धरान, सुनसरी</li>
        <li>११. श्रीमति शान्ता मेन्याङ्ग्वो — धरान, सुनसरी</li>
        <li>१२. श्रीमति तारा मेन्याङ्ग्वो — धरान, सुनसरी</li>
        <li>१३. श्रीमान राम बहादुर मेन्याङ्बो — धरान, सुनसरी</li>
        <li>१४. श्रीमान दिपक मेन्याङ्ग्बो — धरान, सुनसरी</li>
        <li>१५. श्रीमान निर प्रसाद मेन्याङ्बो — धरान, सुनसरी</li>
        <li>१६. श्रीमान अनिल मेन्याङ्बो — इटहरी, सुनसरी</li>
        <li>१७. श्रीमान डम्बर मेन्याङ्बो — झापा</li>
        <li>१८. ङामुकहाङ मेन्याङ्बो —</li>
        <li>१९. श्रीमान याम बहादुर मेन्याङ्बो —</li>
        <li>२०. श्रीमान धन बहादुर मेन्याङ्बो —</li>
        <li>२१. श्रीमान हिमलाल मेन्याङ्बो — थेचम्बु, ताप्लेजुङ</li>
        <li>२२. श्रीमान इन्द्र बहादुर मेन्याङ्बो — झापा</li>
        <li>२३. श्रीमान गणेश बहादुर मेन्याङ्बो — काठमाण्डौ</li>
        <li>सल्लाहकार: श्रीमती भगिश्वर मेन्याङ्वो — ईटहरी, सुनसरी</li>
      </ol>
    </div>
  </section>


</section>
@endsection