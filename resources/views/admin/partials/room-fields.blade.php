<label class="ops-field">DISPLAY NAME<input class="ops-input" type="text" name="name" value="{{ old('name', $room?->name) }}" required maxlength="100"></label>
<label class="ops-field">CATEGORY<input class="ops-input" type="text" name="category" value="{{ old('category', $room?->category) }}" required maxlength="40" placeholder="Regular / VIP / Bootcamp"></label>
<label class="ops-field">NUMBER OF ROOMS<input class="ops-input" type="number" name="quantity" value="{{ old('quantity', $room?->quantity ?? 1) }}" min="1" required></label>
<label class="ops-field">PLAYER CAPACITY<input class="ops-input" type="number" name="player_capacity" value="{{ old('player_capacity', $room?->player_capacity ?? 1) }}" min="1" required></label>
<label class="ops-field">CPU<input class="ops-input" type="text" name="cpu" value="{{ old('cpu', $room?->cpu) }}" required maxlength="120"></label>
<label class="ops-field">GPU<input class="ops-input" type="text" name="gpu" value="{{ old('gpu', $room?->gpu) }}" required maxlength="120"></label>
<label class="ops-field">MEMORY<input class="ops-input" type="text" name="memory" value="{{ old('memory', $room?->memory) }}" required maxlength="80"></label>
<label class="ops-field">MONITOR<input class="ops-input" type="text" name="monitor" value="{{ old('monitor', $room?->monitor) }}" required maxlength="120"></label>
<label class="ops-field">MONITOR HZ<input class="ops-input" type="number" name="monitor_hz" value="{{ old('monitor_hz', $room?->monitor_hz) }}" min="60" max="1000" required></label>
<label class="ops-field">PERIPHERALS<input class="ops-input" type="text" name="peripherals" value="{{ old('peripherals', $room?->peripherals) }}" maxlength="255"></label>
<label class="ops-field">HOURLY RATE (TJS)<input class="ops-input" type="number" name="hourly_rate" value="{{ old('hourly_rate', $room ? number_format($room->hourly_rate_cents / 100, 2, '.', '') : '') }}" min="0.01" step="0.01" required></label>
<label class="ops-field">SORT ORDER<input class="ops-input" type="number" name="sort_order" value="{{ old('sort_order', $room?->sort_order ?? 0) }}" min="0"></label>
<label class="ops-field ops-form-full">DESCRIPTION<textarea class="ops-input" name="description" maxlength="1000" rows="3">{{ old('description', $room?->description) }}</textarea></label>
