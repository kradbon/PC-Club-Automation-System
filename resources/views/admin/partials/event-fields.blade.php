<label>EVENT NAME<input name="title" value="{{ old('title', $event?->title) }}" required maxlength="160"></label>
<label>TYPE<input name="type" value="{{ old('type', $event?->type) }}" required maxlength="40" placeholder="TOURNAMENT / COMMUNITY"></label>
<label>STARTS AT<input type="datetime-local" name="starts_at" value="{{ old('starts_at', $event?->starts_at?->format('Y-m-d\\TH:i') ?? now()->addDay()->format('Y-m-d\\TH:i')) }}" required></label>
<label>ENDS AT<input type="datetime-local" name="ends_at" value="{{ old('ends_at', $event?->ends_at?->format('Y-m-d\\TH:i')) }}"></label>
<label>CAPACITY<input type="number" name="capacity" value="{{ old('capacity', $event?->capacity) }}" min="1"></label>
<label>IMAGE URL<input type="url" name="image_url" value="{{ old('image_url', $event?->image_url) }}" maxlength="255"></label>
<label style="grid-column:1/-1">DESCRIPTION<textarea name="description" rows="3" maxlength="2000">{{ old('description', $event?->description) }}</textarea></label>
